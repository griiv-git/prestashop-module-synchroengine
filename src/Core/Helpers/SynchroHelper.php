<?php
/**
 * This file is part of the Symfony package.
 *
 * (c) Arnaud Scoté <arnaud@griiv.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 **/

namespace Griiv\SynchroEngine\Core\Helpers;

use Symfony\Component\DependencyInjection\ContainerInterface;

class SynchroHelper
{
    /**
     * @var ContainerInterface
     */
    static $container;


    public static function getBatchPath()
    {
        return self::getContainer()->getParameter('gsynchro.batchSynchro');
    }

    public static function getImportPath(): string
    {
        return self::getContainer()->getParameter('gsynchro.importPath');
    }

    public static function getImportPathFixtures(): string
    {
        return self::getContainer()->getParameter('gsynchro.importPathFixtures');
    }

    public static function getImportBackupPath():string
    {
        return self::getContainer()->getParameter('gsynchro.importBackup');
    }

    public static function getExportPath(): string
    {
        return self::getContainer()->getParameter('gsynchro.exportPath');
    }

    public static function getExportBackupPath(): string
    {
        return self::getContainer()->getParameter('gsynchro.exportBackup');
    }

    public static function getLogsPath(): string
    {
        return self::getContainer()->getParameter('gsynchro.logsPath');
    }

    public static function getModule(): \griivsynchroengine
    {
        return \Module::getInstanceByName('griivsynchroengine');
    }

    private static function getContainer()
    {
        if (!self::$container instanceof ContainerInterface) {
            #self::$container = \PrestaShop\PrestaShop\Adapter\SymfonyContainer::getInstance();
            self::$container = \PrestaShop\PrestaShop\Adapter\ContainerBuilder::getContainer('front', _PS_MODE_DEV_);
        }

        return self::$container;
    }

    /**
     * Convert an array into a stdClass()
     *
     * @param   array   $array  The array we want to convert
     *
     * @return  object
     */
    public static function arrayToObject($array)
    {
        // First we convert the array to a json string
        $json = json_encode($array);

        // The we convert the json string to a stdClass()
        $object = json_decode($json);

        return $object;
    }


    /**
     * Convert a object to an array
     *
     * @param   object  $object The object we want to convert
     *
     * @return  array
     */
    public static function objectToArray($object)
    {
        // First we convert the object into a json string
        $json = json_encode($object);

        // Then we convert the json string to an array
        $array = json_decode($json, true);

        return $array;
    }

    public static function slugify($text)
    {
        if (is_string($text)) {
            // replace non letter or digits by -
            $text = preg_replace('~[^\pL\d]+~u', '-', $text);

            // transliterate
            $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

            // remove unwanted characters
            $text = preg_replace('~[^-\w]+~', '', $text);

            // trim
            $text = trim($text, '-');

            // remove duplicate -
            $text = preg_replace('~-+~', '-', $text);

            // lowercase
            $text = strtolower($text);

            if (empty($text)) {
                return 'n-a';
            }

            return $text;
        }
    }

    public static function getLockDirectory()
    {
        return self::getContainer()->getParameter('gsynchro.lockPath');
    }

    public static function notificcationIsEnabled()
    {
        return (bool)self::getContainer()->getParameter('gsynchro.enableNotification');
    }

}
