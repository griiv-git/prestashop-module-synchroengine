<?php
/**
 * This file is part of the Symfony package.
 *
 * (c) Arnaud Scoté <arnaud@griiv.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 **/

namespace Griiv\SynchroEngine\Command;


use Griiv\SynchroEngine\Synchro\Helpers\SynchroHelper;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

final class CreateFtpFoldersCommand extends ContainerAwareCommand
{

    /**
     * @var Filesystem
     */
    private $fs;

    public function __construct($name = null)
    {
        parent::__construct($name);
        $this->fs = new Filesystem();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName('gsynchro:create-ftp-folders')
            ->setDescription('Create synchro ftp folders');
    }

    public static function execAsString($cmd)
    {
        $outputArray = array();
        $command = $cmd . " 2>&1";
        $descriptorspec = array(
            0 => array('pipe', 'r'), // stdin
            1 => array('pipe', 'w'), // stdout
            2 => array('pipe', 'w') // stderr
        );
        $proc = proc_open($command, $descriptorspec, $pipes);
        if (!is_resource($proc))
        {
            $outputArray[] = PHP_EOL . 'ERROR: Can not execute ' . $cmd;
        }
        else
        {
            stream_set_blocking($pipes[2], 0);
            fclose($pipes[0]);

            while (!feof($pipes[1]))
            {
                $s = fread($pipes[1], 512);
                if ($s === false)
                {
                    $outputArray[] = PHP_EOL . 'ERROR: while executing ' . $cmd .': could not read further execution result';
                    break;
                }
                else
                {
                    $outputArray[] = $s;
                }
            }

            $retVal = proc_close($proc);
            if (0 != $retVal)
            {
                $outputArray[] =  PHP_EOL . 'ERROR: invalid exit code ' . $retVal . ' on execute ' . $cmd;
            }
        }
        return implode('', $outputArray);
    }
    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $configFoldersToCreate = [
            'griivsynchroengine.importPath',
            'griivsynchroengine.importBackup',
            'griivsynchroengine.exportPath',
            'griivsynchroengine.exportBackup'
        ];
        $foldersToCreate = [];

        foreach ($configFoldersToCreate as $configName) {
            if ($this->getContainer()->hasParameter($configName)) {
                $foldersToCreate[] = $this->getContainer()->getParameter($configName);
            }
        }

        $this->io->section('These folders will be create : ');
        foreach ($foldersToCreate as $folderToCreate) {
            $this->io->writeln('<info>' . $folderToCreate . '</info>');
        }

        $confirm = $this->io->confirm('Confirm creation folders');

        if ($confirm) {
            foreach ($foldersToCreate as $folderToCreate) {
                if ($this->fs->exists($foldersToCreate)) {
                    $this->io->writeln('<comment> Folder ' . $folderToCreate . ' already exist</comment>');
                    continue;
                }
                $this->fs->mkdir($foldersToCreate);
                $this->io->success('Folder ' . $folderToCreate .' has been created');
            }
        }

        return 0;
    }
}
