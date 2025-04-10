<?php

namespace Griiv\SynchroEngine\Install;

use Griiv\Prestashop\Module\Installer\GriivInstaller;
use PrestaShop\PrestaShop\Core\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\File;

class Installer extends GriivInstaller
{
    protected function installDatabase(): bool
    {
        $file = new File(sprintf('%s/%s/sql/install.sql', _PS_MODULE_DIR_, $this->module->name));

        if ($this->filesystem->exists($file) && $file->isReadable()) {
            $fileContent = file_get_contents($file->getRealPath());
            $fileContent = str_replace(['DB_PREFIX', 'MYSQL_ENGINE'], [_DB_PREFIX_, _MYSQL_ENGINE_], $fileContent);

            if ($file->getSize() > 0) {
                return $this->executeQuery($fileContent);
            } else {
                return true;
            }
        }

        return true;
    }

    /**
     * @return bool
     */
    protected function uninstallDatabase(): bool
    {
        $file = new File(sprintf('%s/%s/sql/uninstall.sql', _PS_MODULE_DIR_, $this->module->name));

        if ($this->filesystem->exists($file) && $file->isReadable()) {
            $fileContent = file_get_contents($file->getRealPath());
            $fileContent = str_replace(['DB_PREFIX', 'MYSQL_ENGINE'], [_DB_PREFIX_, _MYSQL_ENGINE_], $fileContent);

            if ($file->getSize() > 0) {
                return $this->executeQuery($fileContent);
            } else {
                return true;
            }

        }

        return true;
    }
}