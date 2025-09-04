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


final class ListSynchroCommand extends Command
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
            ->setName('gsynchro:list')
            ->setDescription('List all synchros available');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $rows = [];
        foreach ($this->synchros as $service) {
            if (!$service instanceof ExecutableBase) {
                continue;
            }

            $class = get_class($service);
            $shortName = substr($class, strrpos($class, '\\') + 1);
            $type = 'Unknown';

            //test shortname if ends with Import, Export or Sequence
            if (str_ends_with($shortName, 'Import')) {
                $type = 'Import';
            } elseif (str_ends_with($shortName, 'Export')) {
                $type = 'Export';
            } elseif (str_ends_with($shortName, 'Sequence')) {
                $type = 'Sequence';
            }

            $rows[$type][] = [
                $shortName,
                $type,
                $service->getModuleName(),
                'label',
                'description'
            ];
        }
        foreach ($rows as $type => $row) {
            $this->io->section($type . 's');
            $this->io->table(['Class name', 'Type', 'Module', 'Label', 'Description'], $row);
        }

        return 0;
    }
}