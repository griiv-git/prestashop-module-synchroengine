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

use Griiv\SynchroEngine\Core\ExecutableBase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

final class ExecuteCommand extends Command
{

    public function __construct(private iterable $synchros, $name = null)
    {
        parent::__construct($name);
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
            ->setHelp('');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $this->io = new SymfonyStyle($input, $output);
        $classNameToExecute = $input->getArgument('class_name');

        $serviceFound = null;

        foreach ($this->synchros as $service) {
            if (!$service instanceof ExecutableBase) {
                continue;
            }

            $class = get_class($service);
            $shortName = substr($class, strrpos($class, '\\') + 1);

            if ($shortName === $classNameToExecute) {
                $serviceFound = $service;
                break;
            }
        }

        if (null === $serviceFound) {
            $this->io->warning('class ' . $classNameToExecute . ' not exist');
            return 0;
        }

        $this->io->success('Running executable class ' . get_class($serviceFound));

        $serviceFound->execute();

        return 0;
    }
}
