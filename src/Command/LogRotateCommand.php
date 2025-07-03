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

use Griiv\SynchroEngine\Core\Helpers\SynchroHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Filesystem;

final class LogRotateCommand extends Command
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
            ->setName('gsynchro:log-rotate')
            ->setDescription('Compress log files larger than 100MB in logs directory');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $logsPath = SynchroHelper::getLogsPath();
        if (!$this->fs->exists($logsPath)) {
            $io->error(sprintf('Logs path "%s" does not exist', $logsPath));
            return 1;
        }

        $this->finder->files()->name('*.log')->in($logsPath);
        if ($this->finder->count() === 0) {
            $io->warning('No log files found');
            return 0;
        }

        foreach ($this->finder as $file) {
            $this->compressFile($file->getRealPath());
        }

        $io->success('Logs rotated');
        return 0;
    }

    private function compressFile(string $filePath): void
    {
        if (!$this->fs->exists($filePath)) {
            return;
        }

        $threshold = 10 * 1024 * 1024; // 10 MB
        if (filesize($filePath) < $threshold) {
            return;
        }

        $gzFile = $filePath . '.' . date('YmdHis') . '.gz';
        $in = fopen($filePath, 'rb');
        $out = gzopen($gzFile, 'wb9');
        if ($in && $out) {
            while (!feof($in)) {
                gzwrite($out, fread($in, 1024 * 512));
            }
        }
        if ($in) {
            fclose($in);
        }
        if ($out) {
            gzclose($out);
        }

        $this->fs->remove($filePath);
        $this->fs->touch($filePath);
    }
}
