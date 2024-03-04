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

final class AddExportCommand extends Command
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
            ->setName('gsynchro:add-export')
            ->setDescription('add an export file to module')
            ->addArgument('module_name', InputArgument::REQUIRED, 'Module to add the export')
            ->addArgument('export_name', InputArgument::REQUIRED, 'Name of the export');

    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $moduleName = $input->getArgument('module_name');
        $exportName = $input->getArgument('export_name');

        if (!$this->generateClass($moduleName, $exportName)) {
            return 1;
        }

        return 0;
    }

    /**
     * @param string $moduleName
     * @param string $exportName
     * @return bool
     */
    private function generateClass(string $moduleName, string $exportName)
    {
        return $this->generateFileFromTemplate($moduleName, $exportName, ucfirst($exportName) . 'Export.php', 'src/Synchro/Export');
    }

    /**
     * @param string $moduleName
     * @param string $exportName
     * @param string $fileName
     * @param string $destination
     * @return bool
     */
    private function generateFileFromTemplate(string $moduleName, string $exportName, string $fileName, string $destination)
    {
        $absoluteDestination = _PS_MODULE_DIR_ . $moduleName . '/' . $destination;
        if (!$this->fs->exists($absoluteDestination)) {
            $this->fs->mkdir($absoluteDestination);
        }

        $file = $absoluteDestination . '/' . $fileName;

        if ($this->fs->exists($file)) {
            $this->io->warning("File " . $fileName . " already exist");
            return false;
        }

        $moduleName = str_replace('_', ' ', $moduleName);
        $moduleName = ucwords($moduleName);
        $moduleName = str_replace(' ', '', $moduleName);

        $templateContents = file_get_contents(_PS_MODULE_DIR_ . 'griivsynchroengine/config/templates/Export.tpl');
        $templateContents = str_replace('${moduleName}', ucfirst($moduleName), $templateContents);
        $templateContents = str_replace('${exportName}', ucfirst($exportName), $templateContents);

        $this->fs->dumpFile($file, $templateContents);

        $this->io->success('File ' . $file . ' has been created');

        return true;
    }
}
