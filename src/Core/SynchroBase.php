<?php
/**
 * This file is part of the Symfony package.
 *
 * (c) Arnaud Scoté <arnaud@griiv.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 **/

namespace Griiv\SynchroEngine\Core;

use Griiv\SynchroEngine\Core\DataSource\AbstractDataSource;
use Griiv\SynchroEngine\Core\DataSource\DataSourceInterface;
use Griiv\SynchroEngine\Core\Helpers\SynchroHelper;
use Griiv\SynchroEngine\Core\Item\ItemDefinition;
use Griiv\SynchroEngine\Core\Notifier\Notification\ChatNotification;
use Griiv\SynchroEngine\Core\Notifier\Notification\EmailNotification;
use Griiv\SynchroEngine\Exception\BreakException;
use Griiv\SynchroEngine\Core\Item;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

abstract class SynchroBase extends ExecutableBase
{
    protected int $chunkSize = 100;

    protected int $currentRow = 0;

    protected array $dataSources;

    protected DataSourceInterface $currentDataSource;

    protected ItemDefinition $itemDefinition;

    public function __construct($globalParameters = array())
    {
        parent::__construct($globalParameters);

        // Init item definition
        $this->setItemDefinition($this->initItemDefinition());
    }

    protected function _init()
    {
        parent::_init();

        // Init datasources
        $this->setDataSources($this->initDataSources());
    }


    protected function initLogger(): LoggerInterface
    {
        $logger = new Logger(get_class($this));
        $logger->pushHandler(new StreamHandler(SynchroHelper::getLogsPath() . '/' . get_class($this) . '.log'));

        return $logger;
    }

    /**
     * @return Array<DataSourceInterface>
     */
    protected function getDataSources()
    {
        return $this->dataSources;
    }

    /**
     * @param array<DataSourceInterface> $datasources
     * @throws \Exception
     */
    protected function setDataSources(array $datasources)
    {
        foreach($datasources as $ds)
        {
            if (!$ds instanceof DataSourceInterface)
            {
                throw new \Exception("datasources must be an array of zsynchro_DataSource");
            }
        }
        $this->dataSources = $datasources;
    }

    protected function getCurrentDataSource()
    {
        return $this->currentDataSource;
    }

    /**
     * @return ItemDefinition
     */
    protected function getItemDefinition(): ItemDefinition
    {
        return $this->itemDefinition;
    }

    /**
     * @param ItemDefinition $definition
     */
    protected function setItemDefinition(ItemDefinition $definition = null)
    {
        $this->itemDefinition = $definition;
    }

    protected function setCurrentDatasourceName($datasourceName)
    {
        $this->addGlobalParameter('currentDatasourceName', $datasourceName);
    }

    protected function getCurrentDatasourceName()
    {
        return $this->getGlobalParameter('currentDatasourceName');
    }

    protected function getBatchPath()
    {
        return SynchroHelper::getBatchPath();
    }

    protected function run()
    {
        /** @var AbstractDataSource $dataSource */
        foreach ($this->dataSources as $dataSource) {
            $this->currentDataSource = $dataSource;
            $this->setCurrentDatasourceName($dataSource->getEvaluatedName());

            $this->currentRow = $dataSource->getStartRow();
            $processes = [];
            //On lance tout les process
            do {
                $lines = $dataSource->getChunkedCollection($this->currentRow, $this->chunkSize);
                if (count($lines) > 0) {
                    $arguments = array(
                        'class' => get_class($this),
                        'method' => 'chunkRun',
                        'globalParameters' => $this->getGlobalParameters(),
                        'methodParameters' => [$lines],
                        'currentRow' => $this->currentRow,
                        'chunkSize' => $this->chunkSize,
                        'logger' => $this->getLogger(),
                        'moduleName' => $this->moduleName,
                    );

                    $process = $this->execSubprocess($this->getBatchPath(), $arguments);

                    $processes[] = $process;

                    $this->currentRow += $this->chunkSize;
                }
            } while(!empty($lines));

            //On attend que tous les process soit fini pour terminer le script
            while (count($processes) > 0) {
                foreach ($processes as $key => $process) {
                    // Vérifier si le processus est terminé
                    if (!$process->isRunning()) {
                        // Supprimer le processus de la liste des processus en cours
                        unset($processes[$key]);
                    }
                }
                // Attendre un court instant avant de vérifier à nouveau
                usleep(10000); // 10 millisecondes
            }

            $this->callDatacontainerCallback($dataSource, AbstractDataSource::EVENT_END);
        }
    }

    protected function chunkCallback($resultData)
    {
        $resultStatus = $resultData['status'];

        if (!in_array($resultStatus, ['OK', 'END', 'BREAK'])) {
            $this->getLogger()->error($resultData['message']);
            $this->getLogger()->info($resultData['stack']);
        }

        if (in_array($resultStatus, ['PHP_ERROR', 'BREAK'])) {
            //log error in file
            $this->getLogger()->critical(sprintf(
                "Chunk process %s with PID %s (%s)is stopped",
                get_class($this),
                123,
                $this->currentRow
            ));

            if (SynchroHelper::notificcationEmailIsEnabled()) {
                //Notify with email
                $notif = EmailNotification::fromThrowable(new BreakException(
                    sprintf(
                        "Chunk process %s with PID %s (%s)is stopped",
                        get_class($this),
                        123,
                        $this->currentRow
                    ))
                );

                if (isset($resultData['message'])) {
                    $notif->content($resultData['message'] . PHP_EOL . PHP_EOL . $notif->getContent());
                }

                $notif->importanceFromLogLevelName('critical');
                $emails = getenv('GRIIVSYNCHRO_RECIPIENTS_NOTIF');

                foreach (explode(',', $emails) as $email) {
                    $notif->notify(new Notifier\Recipient\Recipient($email));
                }
            }

            if (SynchroHelper::notificationKchatIsEnabled()) {
                $exception = new BreakException(
                    sprintf(
                        "Chunk process %s with PID %s (%s)is stopped",
                        get_class($this),
                        123,
                        $this->currentRow
                    ));
                $kchatApi = new \Griiv\SynchroEngine\Core\Api\KchatApi(
                    getenv('GRIIVSYNCHRO_KCHAT_TOKEN'),
                    getenv('GRIIVSYNCHRO_KCHAT_URL'),
                    '/api/v4/posts'
                );
                $notif = new \Griiv\SynchroEngine\Core\Notifier\Notification\ChatNotification($kchatApi);

                $notif->content($notif->computeExceptionAsString($exception));


                if (isset($resultData['message'])) {
                    $notif->content("**" . $resultData['message'] . "**" . PHP_EOL . PHP_EOL . $notif->getContent());
                }

                $notif->importanceFromLogLevelName('critical');
                $notif->notify(null);
            }

            //Throw exception to stop synchro
            if ($this->getThrowExceptionOnError()) {
                throw new BreakException(
                    sprintf(
                        "Chunk process %s with PID %s (%s)is stopped",
                        get_class($this),
                        123,
                        $this->currentRow
                    ));
            }

            return;
        }

        $chunkSize = $this->chunkSize;
        $begin = $resultData['timeBegin'];
        $end = $resultData['timeEnd'];
        $beginDate = $resultData['dateBegin'];
        $endDate = $resultData['dateEnd'];
        $currentRow = $resultData['currentRow'];
        $toRow = $currentRow + $chunkSize;

        $this->getLogger()->debug("Chunk " . $currentRow . " - " . (string)$toRow .  " executed in ".sprintf('%7.4f', $end - $begin)." secondes");
        $this->getLogger()->debug("Chunk " . $currentRow . " start at " . $beginDate );
        $this->getLogger()->debug("Chunk " . $currentRow . " end at " . $endDate );
    }

    public function chunkRun(array $data)
    {
        $this->getLogger()->info("BEGIN " . __METHOD__);
        $datasourcename = $this->getCurrentDatasourceName();

        foreach ($data as $key => $row) {
            try {
                $item = new \Griiv\SynchroEngine\Core\Item\Item($row, $this->getItemDefinition());
                $this->_processRow($item->getDataArray());

                /*if ($item->isValid()) {
                    $this->_processRow($item->getDataArray());
                } else {
                    foreach($item->getErrorStrings() as $errorString)
                    {
                        //$this->getLogger()->err($errorString, array('code' => 0, 'row' => $key, 'datasource' => $datasourcename));
                    }
                }*/
            } catch(BreakException $e) {
                $this->getLogger()->critical($e->getMessage() .  ' => ' . $e->getTraceAsString(), ['code' => $e->getCode(), 'row' => $key, 'datasource' => $datasourcename]);

                throw $e;
            } catch(\Exception $e) {
                $this->getLogger()->alert($e->getMessage() . ' => ' . $e->getTraceAsString(), ['code' => $e->getCode(), 'row' => $key, 'datasource' => $datasourcename]);
            }
        }
        $this->getLogger()->info("END " . __METHOD__);
    }

    /**
     * @param AbstractDataSource $datacontainer
     */
    protected function callDatacontainerCallback($datacontainer, $event = AbstractDataSource::EVENT_END)
    {
        $callableName = null;

        if(is_callable($datacontainer->getCallBack($event), false, $callableName)) {
            try {
                $result = call_user_func($datacontainer->getCallBack($event), $datacontainer);
                $this->getLogger()->info(sprintf("Callback %s executed successfully | result : %s", $callableName, var_export($result, true)));
            } catch(\Exception $e) {
                $this->getLogger()->warn(sprintf("Unable to call : %s | exception : %s", $callableName, $e->getMessage()), $e->getCode());
            }
        }
    }

    protected function _processRow($dataArray)
    {
        return $this->processRow($dataArray);
    }

    abstract protected function processRow($dataArray);

    abstract protected function initDataSources();
    abstract protected function initItemDefinition();


}
