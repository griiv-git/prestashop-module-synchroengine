
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
* `gsynchro:log-rotate` : Compresser les logs dépassant 100Mo

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

use Griiv\SynchroEngine\Core\ImportBase;

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

### Créer le service associé à l'import

Pour que l'import soit reconnu par le système, il faut créer un service dans le fichier `services.yml` de votre module :

```yaml  
  griiv.synchro.import.customer_import:
    class: Griiv\Synchro\Synchro\Import\CustomerImport
    tags: [ gsynchro.synchro ]
```

## Créer un Export

Pour générer la classe d'export il suffit d'exécuter la commande suivante&nbsp;:

```bash
php bin/console gsynchro:add-export gsynchro NomDeMonExport
```

Un fichier `NomDeMonExportExport.php` est alors créé dans le dossier
`_PS_ROOT_DIR_/modules/griivsynchroengine/src/Synchro/Export/` de votre
module. Pensez à lancer `composer dumpautoload` après la création pour que
l'autoload soit mis à jour.

Le squelette généré ressemble à ceci&nbsp;:

```php
namespace Griiv\SynchroEngine\Synchro\Export;

use Griiv\SynchroEngine\Core\ExportBase;

class NomDeMonExportExport extends ExportBase
{
    protected function initDataTargets()
    {
        // Définir les cibles de données (fichier, API, ...)
    }

    protected function initTargetItemDefinition()
    {
        // Structure des données attendues par la cible
    }

    protected function initDataSources()
    {
        // Sources à exporter (BDD, API, ...)
    }

    protected function initItemDefinition()
    {
        // Définition des données issues de la source
    }

    protected function processRow($dataArray)
    {
        return parent::processRow($dataArray);
    }
}
```

### Les DataSources

Une **DataSource** décrit la manière de lire les données en entrée. Plusieurs
implémentations sont fournies dans `src/Core/DataSource`&nbsp;:

- `FileDataSource` pour lire des fichiers CSV ou texte ;
- `GlobDataSource` afin de traiter un ensemble de fichiers correspondant à un
  motif&nbsp;(`glob`) ;
- `DbQueryDataSource` et `PDODataSource` pour récupérer des données en base ;
- `PrestashopApiDataSource` ou `AkeneoApiRestDataSource` pour des appels API ;
- `BufferDataSource` lorsque les données sont déjà en mémoire.

### Les DataTargets

Une **DataTarget** représente la destination des données lors d'un export.
Quelques classes sont disponibles dans `src/Core/DataTarget`&nbsp;:

- `FileDataTarget` et `PlainFileDataTarget` pour écrire dans des fichiers ;
- `ApiRestDataTarget` pour envoyer les informations vers une API REST ;
- `PDODataTarget` pour insérer ou mettre à jour des lignes en base.

### Sous le capot

Le moteur s'appuie sur `symfony/process` pour paralléliser les traitements.
Chaque lot de données est exécuté dans un sous‑processus via le script
`bin/batchSynchro.php`. Les logs sont gérés par *Monolog* et stockés dans le
répertoire défini par le paramètre `gsynchro.logsPath`. Les fichiers sources
peuvent être archivés automatiquement après traitement et les logs sont
compressés grâce à la commande `gsynchro:log-rotate`.

## TODO

1. [ ] Mise en place Validateur pour ItemProperty
2. [x] Mise en place des séquences
3. [x] Mise en place logrotate fichier de logs
4. [x] Mise en place backup file gzip après import (pour les fichiers)
5. [ ] Rajouter des hooks pour Prestashop
6. [ ] Mise en place rotate pour les fichiers dans.backup
7. [ ] Ajouter Datasource database doctrine ORM 
8. [x] Mise en place service tagé pour identifier les synchros
9. [ ] Mise en place dispatcher d'évènement Symfony
