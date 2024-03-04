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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

final class ExecuteCommand extends Command
{

    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * @var Finder
     */
    private $finder;

    public function __construct($name = null)
    {
        parent::__construct($name);
        $this->fs = new Filesystem();
        $this->finder = new Finder();
    }
    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName('gsynchro:execute')
            ->setDescription('Run an import, export or sequence')
            ->addArgument('class_name', InputArgument::REQUIRED, 'Executable class to run')
            ->setHelp('Help test');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $this->io = new SymfonyStyle($input, $output);
        $classNameToExecute = $input->getArgument('class_name');

        //use Griiv\SynchroEngine\Synchro\Import\CustomerImport;
        $type = $this->getTypeExecutable($classNameToExecute);
        $namespace = "Griiv\\SynchroEngine\\Synchro\\" . $type . "\\";

        $fullClassName = $namespace . $classNameToExecute;

        if (class_exists($fullClassName)) {
            $this->io->success('Running executable class ' . $fullClassName);
            $executable = new $fullClassName();

            $executable->execute();

        } else {
            $this->io->warning('class ' . $classNameToExecute . ' not exist');
        }


        return 0;
    }

    private function getTypeExecutable(string $className)
    {
        if (strpos($className, 'Import')) {
            return 'Import';
        }

        if (strpos($className, 'Export')) {
            return 'Export';
        }

        if (strpos($className, 'Sequence')) {
            return 'Sequence';
        }
    }
}
