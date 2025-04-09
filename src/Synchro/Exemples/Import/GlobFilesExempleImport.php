<?php
/**
 * This file is part of the Symfony package.
 *
 * (c) Arnaud Scoté <arnaud@griiv.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 **/

namespace Griiv\SynchroEngine\Synchro\Exemples\Import;

use FilesystemIterator;
use Griiv\SynchroEngine\Core\DataSource\GlobDataSource;
use Griiv\SynchroEngine\Core\Helpers\SynchroHelper;
use Griiv\SynchroEngine\Core\ImportBase;
use Griiv\SynchroEngine\Core\Item\ItemDefinition;
use Griiv\SynchroEngine\Core\Item\ItemProperty;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

class GlobFilesExempleImport extends ImportBase
{

    protected function initLogger(): LoggerInterface
    {
        $logger = new Logger(get_class($this));
        $logger->pushHandler(new StreamHandler(SynchroHelper::getLogsPath() . '/GlobFilesExempleImport.log'));

        return $logger;
    }

    protected function initDataSources()
    {
        //Le chemin ou sont stocké les PDF _PS_ROOT_DIR_/ftp/import/PDF
        $importPath = SynchroHelper::getImportPath() . DIRECTORY_SEPARATOR . 'PDF';
        $ds = new GlobDataSource($importPath . DIRECTORY_SEPARATOR . "*.pdf", FilesystemIterator::CURRENT_AS_PATHNAME);
        return [$ds];
    }

    protected function initItemDefinition()
    {
        $definition = new ItemDefinition();
        $definition->add(new ItemProperty('filename'));

        return $definition;
    }

    protected function processRow($dataArray)
    {
        $filename = $dataArray["filename"];
        //Faire le traitement du fichier que l'on souhaite le déplacer, relier un fichier facture à une commande par exemple etc..
        $result = $this->methodToDoSomething();
    }

    private function methodToDoSomething()
    {
        //methode

        return true;
    }
}
