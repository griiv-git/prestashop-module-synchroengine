<?php
/**
 * This file is part of the Symfony package.
 *
 * (c) Arnaud Scoté <arnaud@griiv.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 **/

namespace Griiv\SynchroEngine\Synchro;

use Griiv\SynchroEngine\Synchro\DataTarget\DataTargetInterface;
use Griiv\SynchroEngine\Synchro\Item\ItemDefinition;

abstract class PipeSynchro extends SynchroBase
{
    protected int $chunkSize = 1000;

    protected int $maxParallelProcesses = 5;

    /**
     * @var array<DataTargetInterface>
     */
    protected array $dataTargets = [];

    /**
     * @var ItemDefinition
     */
    protected ItemDefinition $targetItemDefinition;

    /**
     * This constructor will be called in main/child processes
     * @throws \Exception
     */
    public function __construct($globalParameters = array())
    {
        parent::__construct($globalParameters);

        // Init target item definition
        $this->setTargetItemDefinition($this->initTargetItemDefinition());
    }

    /**
     * @return array<DataTargetInterface>
     */
    protected function getDataTargets()
    {
        return $this->dataTargets;
    }

    /**
     * @param array<DataTargetInterface> $datatargets
     * @throws \Exception
     */
    protected function setDataTargets(array $datatargets)
    {
        foreach($datatargets as $dt)
        {
            if (!$dt instanceof DataTargetInterface)
            {
                throw new \Exception("datatargets must be an array of zsynchro_DataTarget");
            }
        }
        $this->dataTargets = $datatargets;
    }

    /**
     * @return ItemDefinition
     */
    protected function getTargetItemDefinition()
    {
        return $this->targetItemDefinition;
    }

    /**
     * @param ItemDefinition $definition
     */
    protected function setTargetItemDefinition(ItemDefinition $definition = null)
    {
        $this->targetItemDefinition = $definition;
    }

    public function chunkRun(array $data, $datasourcename = null)
    {
        // Init datatargets
        $this->setDataTargets($this->initDataTargets());

        parent::chunkRun($data, $datasourcename);
    }

    protected function _processRow($dataArray)
    {
        $targetData = $this->processRow($dataArray);
        if($targetData !== null) {
            $item = new Item\Item($targetData, $this->getTargetItemDefinition(), true);
            foreach($this->getDataTargets() as $dataTarget) {
                $dataTarget->addItems(array($item->getDataArray()));
            }
        }
    }

    protected function processRow($dataArray)
    {
        return $dataArray;
    }

    // Abstract methods
    abstract protected function initDataTargets();

    // Abstract methods
    abstract protected function initTargetItemDefinition();
}
