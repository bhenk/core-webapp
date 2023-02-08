<?php

namespace unit;

use bhenk\corewa\conf\Config;
use Exception;
use function define;
use function defined;

/**
 * Running phpunit in CLI (from project root):
 * $ phpunit --bootstrap application/unit/bootstrap.php application/unit
 *
 * Running phpunit from phpStorm:
 * make sure this file is set in settings>PHP>Test Frameworks,
 * 'Default bootstrap file' (under Test Runner).
 */
defined("APPLICATION_ROOT")
or define("APPLICATION_ROOT", realpath(dirname(__DIR__)));

$vendor_autoload = dirname(__DIR__, 2)
    . DIRECTORY_SEPARATOR . "vendor"
    . DIRECTORY_SEPARATOR . "autoload.php";

spl_autoload_register(function ($para) {
    $path = APPLICATION_ROOT . DIRECTORY_SEPARATOR
        . str_replace('\\', DIRECTORY_SEPARATOR, $para) . '.php';
    if (file_exists($path)) {
        include $path;
        return true;
    }
    return false;
});
require_once $vendor_autoload;

$config_file = __DIR__ . DIRECTORY_SEPARATOR . "unit_config.php";
if (1 == 0) {
    echo "\nBootstrapping from '" . __FILE__ . "'";
    echo "\napplication root = '" . APPLICATION_ROOT . "'";
    echo "\nvendor autoload  = '" . $vendor_autoload . "'";
    echo "\nglobal config    = '" . $config_file . "'";
    echo "\n\n";
}

try {
    Config::load(APPLICATION_ROOT, $config_file);
} catch (Exception $e) {
    exit($e->getMessage());
}

date_default_timezone_set('Europe/Amsterdam');

