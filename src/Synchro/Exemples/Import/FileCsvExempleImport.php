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

use Griiv\SynchroEngine\Core\Helpers\DataSourceHelper;
use Griiv\SynchroEngine\Core\Helpers\SynchroHelper;
use Griiv\SynchroEngine\Core\ImportBase;
use Griiv\SynchroEngine\Core\Item\ItemDefinition;
use Griiv\SynchroEngine\Core\Item\ItemProperty;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

class FileCsvExempleImport extends ImportBase
{

    protected function initLogger(): LoggerInterface
    {
        $logger = new Logger(get_class($this));
        $logger->pushHandler(new StreamHandler(SynchroHelper::getLogsPath() . '/FileCsvExempleImport.log'));

        return $logger;
    }

    protected function initDataSources()
    {
        //Le helper va créer un tableau de DataSource en fonction des fichiers qu'il retrouve selon le modèle RAL_*.csv dans le dossier ftp/import
        $datasources = DataSourceHelper::buildFileDataSources('RAL_*.csv', ['delimiter' => ';', 'avoidFirstLine' => true]);
        return $datasources;
    }

    protected function initItemDefinition()
    {
        //En partant du principe que le fichier CSV a les colones suivantes Reference module, Reference table, Reference colonne, Numero ligne, Type definition, Valeur
        $definition = new ItemDefinition();
        $definition->add(new ItemProperty('reference_module'));
        $definition->add(new ItemProperty('reference_table'));
        $definition->add(new ItemProperty('reference_colonne'));
        $definition->add(new ItemProperty('numero_ligne'));
        $definition->add(new ItemProperty('type_definition'));
        $definition->add(new ItemProperty('valeur'));

        return $definition;
    }

    protected function processRow($dataArray)
    {
        $module = $dataArray['reference_module'];
        $table = $dataArray['reference_table'];
        $colone = $dataArray['reference_colonne'];
        $numLigne = $dataArray['numero_ligne'];
        $typeDef = $dataArray['type_definition'];
        $valeur = $dataArray['valeur'];

        $result = $this->methodToDoSomething();
    }

    private function methodToDoSomething()
    {
        //methode pour faire des traitements insert en bdd ou update etc.

        return true;
    }
}
