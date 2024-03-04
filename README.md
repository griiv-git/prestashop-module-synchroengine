
# Griiv Synchro Engine

Le module de synchro Griiv est utilisé pour créer des flux d'import, export via des fichiers CSV, fichier texte, API Prestashop, BDD, API REST, etc...

Il permet également de traiter de gros volumes de données avec une parallélisation de plusieurs sous tâche pour traiter les données entrantes. 

Par exemple un fichier CSV de 1 000 000 peut être traité via 100 sous taĉhes lancées en parallèle qui traite chacune 10 000 entrées de la source de donnée entrante

La librairie `symfony/process` est utilisé pour faire cette parallélisation.

## Commandes Symfony disponible

* `gsynchro:add-export` : Créer un export
* `gsynchro:add-import` : Créer un import
* `gsynchro:add-sequence` : Créer une séquence
* `gsynchro:create-ftp-folders` : Crée la structure des dossiers pour le ftp
* `gsynchro:execute` : Lancer un import, un export ou une séquence

Utilisation pour la crontab notamment `php bin/console gsynchro:execute MonImport`

## Configuration générale `modules/griivsynchroengine/config/services.yml`

* `gsynchro.importPath` : Chemin du ftp ou sont mis à disposition les fichiers à importer
* `gsynchro.importBackup` : Chemin du ftp ou sont sauvegardé les fichiers qui ont été importé
* `gsynchro.exportPath` : Chemin du ftp ou sont mis à disposition les fichiers pour les services externe
* `gsynchro.exportBackup` : Chemin du ftp ou sont sauvegardés les fichiers exportés
* `gsynchro.logsPath` : Chemin du dossier qui contient les logs
* `gsynchro.batchSynchro` : Chemin de l'executable PHP pour les sous tâches

## Créer un Import

Pour créer un import :

Si la dossier n'existe pas à la racine du projet, d'abord lancer la commande  `php bin/console gsynchro:create-ftp-folders`

Lancer la commande `php bin/console gsynchro:add-import gsynchro Customers`

Le fichier suivant est crée : `_PS_ROOT_DIR_/modules/griivsynchroengine/src/Synchro/Import/CustomersImport.php`

**Pensez à faire un `composer dumpautoload` dans votre module pour mettre à jour l'autoloading**

Voici le contenu fichier crée :
```php
namespace Griiv\SynchroEngine\Synchro\Import;

use Griiv\SynchroEngine\Synchro\ImportBase;

class CustomersImport extends ImportBase
{
    protected function initDataSources()
    {
        // TODO: Implement initDataSources() method.
    }
    
    protected function initItemDefinition()
    {
        // TODO: Implement initItemDefinition() method.
    }
    
    protected function processRow($dataArray)
    {
        // TODO: Implement processRow() method.
    }
}
```
La méthode `initDataSources()` permet d'initialisé la ou les sources de données que l'on souhaite utilisé pour l'import (Fichier, Api, Requete BDD, etc)

La méthode `initItemDefinition()` permet de définir la structure de la source de donnée, pour un fichier CSV par exemple cela va être le nom des colonnes du fichier traité

La méthode `processRow()` cette méthode sera appelé pour traité un jeu de donnée source

Il est également possible pour chaque import de surcharger les méthodes suivantes :

`initLogger()` : permet d'initialiser un logger pour l'import. De base un fichier de log est pour chaque import voir ```SynchroBase::initLogger()```



## Créer un Export

## Les DataSources

## Les DataTargets

## Sous le capot

## TODO

1. [ ] Mise en place Validateur pour ItemProperty
2. [ ] Mise en place des séquences 
3. [ ] Mise en place logrotate fichier de logs
4. [ ] Mise en place backup file gzip après import (pour les fichiers)
5. [ ] Rajouter des hooks pour Prestashop
6. [ ] Mise en place rotate pour les fichiers dans.backup
