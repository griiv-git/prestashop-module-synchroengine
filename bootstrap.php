<?php

$rootDir = defined('_PS_ROOT_DIR_') ? _PS_ROOT_DIR_ : getenv('_PS_ROOT_DIR_');
if (!$rootDir) {
    $rootDir = __DIR__ . '/../../';
}

require_once $rootDir . '/vendor/autoload.php';

(new \Symfony\Component\Dotenv\Dotenv(true))->loadEnv(__DIR__ . '/.env');
