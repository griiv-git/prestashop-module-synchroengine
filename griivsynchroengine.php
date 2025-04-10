<?php
/**
 * This file is part of the Symfony package.
 *
 * (c) Arnaud Scoté <arnaud@griiv.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 **/

use Symfony\Component\Dotenv\Dotenv;

require_once __DIR__ . '/vendor/autoload.php';

class griivsynchroengine extends Module
{

    public function __construct()
    {
        $this->name = 'griivsynchroengine';
        $this->version = '1.1.0';
        $this->author = 'Griiv';

        parent::__construct();

        $this->displayName = $this->trans('Griiv Synchro', [], self::getTranslationDomain());
        $this->description = $this->trans('Set of command and codes to create imports and exports', [], self::getTranslationDomain());
        $this->ps_versions_compliancy = [
            'min' => '1.7.5.0',
            'max' => _PS_VERSION_,
        ];

        $this->loadEnv();
    }

    public function install()
    {
        $installer = new Griiv\SynchroEngine\Install\Installer($this);
        return parent::install() && $installer->install();
    }

    public function uninstall()
    {
        return parent::uninstall();
    }

    public static $kernel;

    public static function getKernel()
    {
        // if the singleton doesn't exist
        if (!self::$kernel) {
            require_once _PS_ROOT_DIR_.'/app/AppKernel.php';
            $env = _PS_MODE_DEV_ ? 'dev' : 'prod';
            $debug = _PS_MODE_DEV_ ? true : false;
            self::$kernel = new \AppKernel($env, $debug);
            self::$kernel->boot();
        }

        return self::$kernel;
    }

    /**
     * Get a specific Symfony service.
     *
     * @param string $service
     *
     * @return object
     */
    public static function getService(string $service)
    {
        return self::getKernel()->getContainer()->get($service);
    }

    public static function getParameter(string $key): string
    {
        return self::getKernel()->getContainer()->getParameter($key);
    }

    public static function getEntityManager(): \Doctrine\ORM\EntityManagerInterface
    {
        return self::getService('doctrine.orm.entity_manager');
    }


    public function isUsingNewTranslationSystem()
    {
        return true;
    }

    public static function getTranslationDomain()
    {
        return "Modules.Griivsynchroengine.Griivsynchroengine";
    }

    private function loadEnv(): void
    {
        $dotenv = new Dotenv(true);
        $dotenv->loadEnv(__DIR__ . '/.env');
    }
}
