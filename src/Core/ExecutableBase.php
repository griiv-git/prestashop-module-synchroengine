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


use PrestaShop\PrestaShop\Core\Util\DateTime\DateTime;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

abstract class ExecutableBase
{
    protected LoggerInterface $logger;

    /**
     * @var boolean
     */
    protected $throwExceptionOnError = false;

    /**
     * @var array
     */
    protected $globalParameters = array();

    public function __construct(array $globalParameters = [])
    {
        $logger = $this->initLogger();

        $this->setLogger($logger);
    }

    public function __destruct()
    {
        // Nothing to do...
    }

    protected function _init()
    {
        $this->init();
    }

    protected function init()
    {
        // Override this...
    }

    protected function shutdown()
    {
        // Override this...
    }

    protected function _shutdown()
    {
        try
        {
            $this->shutdown();
        }
        catch(\Exception $e)
        {
           //log exceptio with logger
        }
    }

    public function getGlobalParameters(): array
    {
        return $this->globalParameters;
    }

    /**
     * @param $parameterName
     * @return mixed|null
     */
    public function getGlobalParameter($parameterName)
    {
        return $this->globalParameters[$parameterName] ?? null;
    }

    public function addGlobalParameter($key, $value): void
    {
        $this->globalParameters[$key] = $value;
    }

    public function setGlobalParameters(array $parameters): void
    {
        $this->globalParameters = $parameters;
    }

    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function execute()
    {
        try {
            // Initialize synchro (main process)
            $this->_init();

            $this->getLogger()->info(get_class($this) . " BEGIN");

            // Launch pre run
            if ($this->preRun() === true){
                // Launch default run
                $this->run();
                // Launch post run
                $this->postRun();
            }

            $this->getLogger()->info(get_class($this) . " END");

            // Shutdown (main process)
            $this->_shutdown();

        } catch(\Exception $e) {
            $this->getLogger()->alert($e->getMessage() . "\n" . $e->getTraceAsString());
            $this->getLogger()->info(get_class($this) . " END WITH ERROR");
            $this->_shutdown();
            throw $e;
        }
    }

    protected function preRun()
    {
        return true;
    }

    /**
     * post run
     * @return void
     */
    protected function postRun()
    {
        // override this
    }

    public function setThrowExceptionOnError(bool $throwExceptionOnError): void
    {
        $this->throwExceptionOnError = (bool) $throwExceptionOnError;
    }

    /**
     * @return boolean
     */
    public function getThrowExceptionOnError(): bool
    {
        return $this->throwExceptionOnError;
    }

    public function execSubprocess($batchPath, $arguments)
    {
        $result = ['status' => 'BREAK', 'data' => [], 'message' => ''];

        $begin = microtime(true);
        $beginDate = (new \DateTime())->format('Y-m-d/m/y H:i:s');

        $tmpFilePath = tempnam('/tmp', 'gsynchro_');
        $phpBinaryPath = (new PhpExecutableFinder())->find();
        file_put_contents($tmpFilePath, serialize([$arguments]), 3);
        $cmd = $phpBinaryPath . ' ' . $batchPath . ' "' . $tmpFilePath . '"';

        $process = Process::fromShellCommandline($cmd);
        //$process = new Process($cmd);

        $that = $this;
        $process->start(function($type, $buffer) use ($that, $begin, $process, $beginDate) {
            if (Process::ERR === $type) {
                // Gérer la sortie d'erreur
                $that->getLogger()->error($buffer);
            } else {

                // Gérer la sortie standard
                $end = microtime(true);
                $endDate = (new \DateTime())->format('d/m/y H:i:s');
                $result = json_decode($buffer, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    $this->getLogger()->error($buffer);
                    //throw new \Exception(var_export($buffer, true));
                    $result['status'] = "BREAK";
                }


                $result['timeBegin'] = $begin;
                $result['timeEnd'] = $end;
                $result['dateBegin'] = $beginDate;
                $result['dateEnd'] = $endDate;

                if (!isset($result['status'])) {
                    $result['status'] = 'BREAK';
                }


                $this->chunkCallback($result);
            }
        });

        return $process;
    }

    protected function getJsonErrorName($code)
    {
        switch ($code)
        {
            case JSON_ERROR_NONE:
                return 'JSON_ERROR_NONE';
            case JSON_ERROR_STATE_MISMATCH:
                return 'JSON_ERROR_STATE_MISMATCH';
            case JSON_ERROR_CTRL_CHAR:
                return 'JSON_ERROR_CTRL_CHAR';
            case JSON_ERROR_SYNTAX:
                return 'JSON_ERROR_SYNTAX';
            case JSON_ERROR_UTF8:
                return 'JSON_ERROR_UTF8';
            default:
                return 'JSON_ERROR_UNKNOWN';
        }
    }

    abstract protected function run();
    abstract protected function initLogger();
}
