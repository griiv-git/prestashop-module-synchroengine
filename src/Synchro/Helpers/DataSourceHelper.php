<?php
/**
 * This file is part of the Symfony package.
 *
 * (c) Arnaud Scoté <arnaud@griiv.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 **/

namespace Griiv\SynchroEngine\Synchro\Helpers;

use Griiv\SynchroEngine\Synchro\DataSource\DataSourceInterface;
use Griiv\SynchroEngine\Synchro\DataSource\FileDataSource;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

class DataSourceHelper
{
    /**
     * Build file datasources array
     * @param string $filenamePattern (example: file_*{foo,bar}.csv)
     * @param array $params (params used by datasource constructor)
     * @param ?boolean $backup
     * @param ?string $directory
     * @return array<DataSourceInterface>
     */
    public static function buildFileDataSources(string $filenamePattern, array $params = [], bool $backup = null, string $directory = null): array
    {
        if (!isset($directory)) {
            $directory = SynchroHelper::getImportPath();
        }

        if (!is_readable($directory)) {
            throw new \Exception(sprintf("%s | Import directory is not READABLE : %s", __METHOD__, $directory));
        }

        // Retrieve files
        $files = glob($directory . DIRECTORY_SEPARATOR . $filenamePattern);

        $backupFileFormat = 'gzip';

        if ($backup === null) {
            $backup = strtolower($backupFileFormat);
        }

        $backup = ($backup === true) ? 'gzip' : $backup;
        $backup = ($backup === false) ? 'none' : $backup;

        // Init backup callback
        switch ($backup) {
            case 'none':
                $callback = null;
                break;
            case 'plain':
                $callback = ["Griiv\SynchroEngine\Synchro\Helpers\DataSourceHelper", "backupFileDataSource"];
                break;
            case 'gzip':
            default:
                $callback = ["Griiv\SynchroEngine\Synchro\Helpers\DataSourceHelper", "backupFileGzippedDataSource"];
        }

        // Make datasources array
        $datasources = array();

        foreach ($files as $file) {
            $params['fileName'] = $file;
            $ds = new FileDataSource($params);
            $ds->registerCallBack($callback);
            $datasources[] = $ds;
        }

        return $datasources;
    }

    /**
     * Backup source file
     * @param FileDataSource $fileDataSource
     * @param string $destination
     * @return boolean
     */
    public static function backupFileDataSource(FileDataSource $fileDataSource, $destination = null)
    {
        $source = $fileDataSource->getFileName();
        return self::backupFile($source, $destination, false);
    }

    /**
     * Backup source file
     * @param FileDataSource $fileDataSource
     * @param string $destination
     * @return boolean
     */
    public static function backupFileGzippedDataSource(FileDataSource $fileDataSource, $destination = null)
    {
        $source = $fileDataSource->getFileName();
        return self::backupFile($source, $destination, true);
    }

    public static function backupFile($filename, $destination = null, $gzip = false)
    {
        $fs = new Filesystem();
        if (!isset($destination)) {
            // Mkdir if necessary
            $destinationFolder = SynchroHelper::getImportBackupPath();
            if (!file_exists($destinationFolder)) {
                $fs->mkdir($destinationFolder);
            }

            // Generate backup filename
            $file = new File($filename);
            $ext = $file->getExtension();
            $destination = $destinationFolder . DIRECTORY_SEPARATOR . basename($file->getBasename(), ".".$file->getExtension()) . uniqid("_backup-") . "." . $ext;
        }

        // Move file
        if ($gzip) {
            rename($filename, $destination);
            return self::gzipFile($destination);
        } else {
            return rename($filename, $destination);
        }
    }

    public static function gzipFile($filename)
    {
        if (!file_exists($filename)) {
            return false;
        }

        $return = 0;
        $output = array();

        $result = exec("gzip $filename 2>&1", $output, $return);

        if ($return !== 0) {
            throw new \Exception($result);
        }

        return $return;
    }
}
