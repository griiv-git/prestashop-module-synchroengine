<?php
/**
 * This file is part of the Symfony package.
 *
 * (c) Arnaud Scoté <arnaud@griiv.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 **/

require_once __DIR__ . '/../../../config/config.inc.php';
require_once __DIR__ .'/../vendor/autoload.php';

define("STATUS_OK", "OK");
define("STATUS_BREAK", "BREAK");
define("STATUS_END", "END");

ini_set('mysql.connect_timeout', 28800);
ini_set('default_socket_timeout', 28800);

$argv = unserialize(file_get_contents($argv[1]));
$arguments = $argv[0];

$class = $arguments['class'];
$method = $arguments['method'];
$globalParameters = isset($arguments['globalParameters']) ? $arguments['globalParameters'] : array();

// Make method parameters array
$methodParameters = isset($arguments['methodParameters']) ? $arguments['methodParameters'] : array();


$status = STATUS_END;
$data = [];
$message = "";
$stack = "";

if (is_null($class)) {
    $status = STATUS_BREAK;
    $message = 'Class pass in parameter is null';
} else {
    if (class_exists($class)) {
        $instance = new $class($globalParameters);
        if ($instance !== null && $instance instanceof Griiv\SynchroEngine\Synchro\ExecutableBase) {

            try {
                $data = call_user_func_array(array($instance, $method), $methodParameters);

                $status = STATUS_OK;
            } catch(Griiv\SynchroEngine\Exception\BreakException $e) {
                $status = STATUS_BREAK;
                $message = $e->getMessage();
            }
        } else {
            $message = addslashes($class) . " is not zsynchro_ExecutableBase";
            $status = STATUS_BREAK;
        }
    } else {
        $message = addslashes($class) . " not exist";
        $status = STATUS_BREAK;
    }
}

$result = [
    'status' => $status,
    'data' => $data,
    'message' => $message,
    'currentRow' => $arguments['currentRow']
];

echo json_encode($result);
