<?php

namespace unit;

use bhenk\core\conf\Config;

/**
 * Running phpunit from commandline:
 * $ phpunit --bootstrap unit/autoload.php unit
 *
 * Running phpunit from phpStorm:
 * make sure this file is set in settings>PHP>Test Frameworks,
 * 'Default bootstrap file' (under Test Runner).
 */
defined("APPLICATION_ROOT")
or define("APPLICATION_ROOT", realpath(dirname(__DIR__)));

$vendor_autoload = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

//echo "\nBootstrapping from '".__FILE__."'\n";
//echo "\napplication root = '".APPLICATION_ROOT."'";
//echo "\nvendor autoload  = '".$vendor_autoload."'\n";

spl_autoload_register(function ($para) {
    $path = APPLICATION_ROOT . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $para) . '.php';
    if (file_exists($path)) {
        include $path;
        return true;
    }
    return false;
});
require_once $vendor_autoload;

Config::load(__DIR__ . DIRECTORY_SEPARATOR . "global_config.php");

date_default_timezone_set('Europe/Amsterdam');
