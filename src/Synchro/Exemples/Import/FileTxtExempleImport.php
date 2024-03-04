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

use Griiv\SynchroEngine\Synchro\Helpers\SynchroHelper;
use Griiv\SynchroEngine\Synchro\Helpers\DataSourceHelper;
use Griiv\SynchroEngine\Synchro\ImportBase;
use Griiv\SynchroEngine\Synchro\Item\ItemDefinition;
use Griiv\SynchroEngine\Synchro\Item\ItemProperty;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

class FileTxtExempleImport extends ImportBase
{

    protected function initLogger(): LoggerInterface
    {
        $logger = new Logger(get_class($this));
        $logger->pushHandler(new StreamHandler(SynchroHelper::getLogsPath() . '/FileTxtExempleImport.log'));

        return $logger;
    }

    protected function initDataSources()
    {
        //Le helper va créer un tableau de DataSource en fonction des fichiers qu'il retrouve selon le modèle RAL_*.csv dans le dossier ftp/import
        //avoidFirstLine permet de ne pas traiter la première ligne du fichier, a mettre à true si sur la premiere ligne du fichier il y a le nom des colones
        $datasources = DataSourceHelper::buildFileDataSources('RAL_*.txt', ['delimiter' => '|', 'avoidFirstLine' => true]);
        return $datasources;
    }

    protected function initItemDefinition()
    {
        //En partant du principe que le fichier TXT a les colones suivantes Reference module, Reference table, Reference colonne, Numero ligne, Type definition, Valeur
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
