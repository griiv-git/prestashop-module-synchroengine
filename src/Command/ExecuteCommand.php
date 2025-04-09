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

ini_set('memory_limit', '-1');

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
            ->addArgument('module_name', InputArgument::REQUIRED, 'Module to where is the class')
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
        $module = $input->getArgument('module_name');

        $type = $this->getTypeExecutable($classNameToExecute);

        if (file_exists('modules/' . $module . '/src/Synchro/'.$type.'/' . $classNameToExecute . '.php')) {
            //get namespace in the file class
            $namespace = $this->getNamespaceFromFile('modules/' . $module . '/src/Synchro/'.$type.'/' . $classNameToExecute . '.php');

            $fullClassName = $namespace . "\\" .  $classNameToExecute;

            if (class_exists($fullClassName)) {
                $this->io->success('Running executable class ' . $fullClassName);
                $executable = new $fullClassName();

                $executable->execute();

            } else {
                $this->io->warning('class ' . $classNameToExecute . ' not exist');
            }


        } else {
            $this->io->warning('File not exist');
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

    private function getNamespaceFromFile($filePath) {
        // Lire le contenu du fichier
        $fileContent = file_get_contents($filePath);
        if ($fileContent === false) {
            return null;
        }

        // Utiliser une expression régulière pour trouver le namespace
        $namespacePattern = '/namespace\s+([a-zA-Z0-9_\\\\]+)\s*;/';
        if (preg_match($namespacePattern, $fileContent, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
