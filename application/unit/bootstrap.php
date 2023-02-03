<?php

namespace unit;

use bhenk\corewa\conf\Config;
use function define;
use function defined;

/**
 * Running phpunit from CLI:
 * $ phpunit --bootstrap unit/autoload.php unit
 *
 * Running phpunit from phpStorm:
 * make sure this file is set in settings>PHP>Test Frameworks,
 * 'Default bootstrap file' (under Test Runner).
 */
defined("APPLICATION_ROOT")
or define("APPLICATION_ROOT", realpath(dirname(__DIR__)));

$vendor_autoload = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

spl_autoload_register(function ($para) {
    $path = APPLICATION_ROOT . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $para) . '.php';
    if (file_exists($path)) {
        include $path;
        return true;
    }
    return false;
});
require_once $vendor_autoload;

defined("UNIT_IS_LOUD")
or define("UNIT_IS_LOUD", false);

$config_file = __DIR__ . DIRECTORY_SEPARATOR . "unit_config.php";
Config::load(APPLICATION_ROOT, $config_file);

date_default_timezone_set('Europe/Amsterdam');

if (UNIT_IS_LOUD) {
    echo "\nBootstrapping from '" . __FILE__ . "'";
    echo "\napplication root = '" . APPLICATION_ROOT . "'";
    echo "\nvendor autoload  = '" . $vendor_autoload . "'";
    echo "\nglobal config    = '" . $config_file . "'";
    echo "\n\n";
}
