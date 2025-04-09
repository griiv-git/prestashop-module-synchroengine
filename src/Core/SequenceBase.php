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


use Exception;
use Griiv\SynchroEngine\Core\Helpers\SynchroHelper;
use Griiv\SynchroEngine\Exception\BreakException;
use Symfony\Component\Filesystem\Filesystem;

abstract class SequenceBase extends ExecutableBase
{
    /**
     * @var array<SynchroBase>
     */
    protected array $syncs = [];

    protected $lockFileName;
    protected $runningFileName;

    const READY = 0;
    const RUNNING = 1;
    const LOCKED = 2;
    const DEAD = 3;
    const ZOMBIE = 4;

    private Filesystem $fs;


    public function __construct(array $globalParameters = [])
    {
        parent::__construct($globalParameters);
        $this->fs = new Filesystem();
    }

    /**
     * @return string
     */
    protected function getLockFileName()
    {
        return get_class($this) . ".lock";
    }

    /**
     * @return string
     */
    protected function getRunningFileName()
    {
        return get_class($this) . ".run";
    }

    /**
     * @return string
     */
    protected function getRunningFilePath()
    {
        return SynchroHelper::getLockDirectory() . DIRECTORY_SEPARATOR . $this->getRunningFileName();
    }


    /**
     * @return string
     */
    protected function getLockFilePath()
    {
        return SynchroHelper::getLockDirectory() . DIRECTORY_SEPARATOR . $this->getLockFileName();
    }

    /**
     * @param SynchroBase $sync
     */
    public function addSync(SynchroBase $sync)
    {
        $this->syncs[] = $sync;
    }

    /**
     * @return array<SynchroBase>
     */
    public function getSyncs()
    {
        return $this->syncs;
    }

    /**
     * @param array<SynchroBase> $syncs
     * @throws \Exception
     */
    public function setSyncs(array $syncs)
    {
        foreach ($syncs as $sync) {
            if (!$sync instanceof SynchroBase) {
                throw new \Exception("Sync must be an instance of zsynchro_ExecutableBase");
            }
        }

        $this->syncs = $syncs;
    }

    public function preRun()
    {
        $status = $this->getStatus();

        switch ($status) {
            case self::READY:
                $this->getLogger()->info("Sequence " . get_class($this) . " is ready");
                return $this->onReady();
            case self::RUNNING:
                $this->getLogger()->notice("Sequence " . get_class($this) . " is already running.", array('code' => 403));
                return $this->onRunning();
            case self::LOCKED:
                $this->getLogger()->err("Sequence " . get_class($this) . " is locked. Use zsynchro.unlock-sequence " . get_class($this) . " to unlock it.", array('code' => 403));
                return $this->onLocked();
            case self::DEAD:
                $this->getLogger()->err("Sequence " . get_class($this) . " is dead.", array('code' => 403));
                return $this->onDead();
            case self::ZOMBIE:
                $this->getLogger()->crit("Sequence " . get_class($this) . " is a zombie.", array('code' => 403));
                return $this->onZombie();
        }
    }

    public function run()
    {
        $this->flagAsRunning();

        foreach ($this->syncs as $index => $sync) {
            $arguments = array(
                'class' => get_class($this),
                'method' => 'subRun',
                'globalParameters' => $this->getGlobalParameters(),
                'currentRow' => $index,
                'methodParameters' => array(get_class($sync), $sync->getGlobalParameters(), $sync->getThrowExceptionOnError()),
                'module' => SynchroHelper::getModule(),
            );

            $process = $this->execSubprocess($this->getBatchPath(), $arguments);

            $processes[] = $process;
        }

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

        $this->flagAsNotRunning();
    }

    protected function chunkCallback($resultData)
    {
        $this->getLogger()->debug(var_export($resultData, true));
    }

    public function subRun($syncClassName, $syncGlobalParameters, $throwExceptionOnError)
    {
        if (!class_exists($syncClassName)) {
            $this->getLogger()->warn($syncClassName . " doesn't exist");
            return;
        }
        $sync = new $syncClassName($syncGlobalParameters);

        if (!($sync instanceof ExecutableBase)) {
            $this->getLogger()->warn("instance undefined");
            return;
        }

        $sync->setThrowExceptionOnError($throwExceptionOnError);

        switch (true) {
            case $sync instanceof SequenceBase:
                $type = 'sequence';
                break;
            case $sync instanceof ImportBase:
                $type = 'import';
                break;
            case $sync instanceof ExportBase:
                $type = 'export';
                break;
            case $sync instanceof SynchroBase:
                $type = 'synchro';
                break;
            default:
                $type = 'executable';
        }

        $this->getLogger()->info("execute $syncClassName $type");

        try {
            $sync->execute();
        } catch (BreakException $e) {
            $this->getLogger()->crit($e->getMessage(), array('code' => $e->getCode(), 'synchro' => $syncClassName));
            throw $e;
        } catch (Exception $e) {
            $this->getLogger()->warn($e->getMessage(), array('code' => $e->getCode(), 'synchro' => $syncClassName));
        }
    }

    protected function getBatchPath()
    {
        return SynchroHelper::getBatchPath();
    }

    public function hasLockFlag()
    {
        return file_exists($this->getLockFilePath());
    }

    public function lock($e = null)
    {
        if (!$this->hasLockFlag()) {
            $lockDirectory = SynchroHelper::getLockDirectory();
            $this->fs->mkdir($lockDirectory);
            $this->fs->dumpFile($this->getLockFilePath(), var_export($e, true));
        }

        $this->flagAsNotRunning();
    }

    public function unlock()
    {
        if ($this->hasLockFlag()) {
            $this->fs->remove($this->getLockFilePath());
        }
    }

    public function getRunningPid()
    {
        if (file_exists($this->getRunningFilePath())) {
            return file_get_contents($this->getRunningFilePath());
        }
        return null;
    }

    public function flagAsRunning()
    {
        $lockDirectory = SynchroHelper::getLockDirectory();
        $this->fs->mkdir($lockDirectory);
        $this->fs->dumpFile($this->getRunningFilePath(), getmypid());
    }

    public function flagAsNotRunning()
    {
        $this->fs->remove($this->getRunningFilePath());
    }

    public function getStatus()
    {
        $myPid = getmypid();
        $status = self::READY;

        exec("pgrep php", $pids);

        $declaredPid = $this->getRunningPid();

        if ($declaredPid) {
            if (in_array($declaredPid, $pids)) {
                return self::RUNNING;
            } else {
                $status = self::DEAD;
            }
        }

        $mySign = file_get_contents("/proc/$myPid/cmdline");

        foreach ($pids as $pid) {
            $sign = file_get_contents("/proc/$pid/cmdline");
            if ($sign == $mySign) {
                if ($pid != $myPid) {
                    return self::ZOMBIE;
                }
            }
        }

        if ($this->hasLockFlag()) {
            return self::LOCKED;
        }

        return $status;
    }

    public function onReady()
    {
        return true;
    }

    public function onRunning()
    {
        return false;
    }

    public function onLocked()
    {
        return false;
    }

    public function onDead()
    {
        $this->lock();
        return false;
    }

    public function onZombie()
    {
        $this->lock();
        return false;
    }

}
