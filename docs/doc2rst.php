<?php

function stdErr(string $err): void {
    fwrite(STDERR, "\033[1m\033[48;5;15m\033[38;5;124m"
        . $err
        . "\033[0m"
        . PHP_EOL);
}

function stdOut(string $out): void {
    fwrite(STDOUT, $out . PHP_EOL);
}

// read args
if (isset($argv[1])) {
    parse_str($argv[1], $output);
    if (isset($output["--configuration_file"])) {
        $config_file = $output["--configuration_file"];
        if ($config_file != "" and !file_exists($config_file)) {
            stdErr("Configuration file not found: " . $config_file);
            exit(1);
        }
    }
}
if (!isset($config_file) or $config_file == "") $config_file = "./conf.php";

// try read configuration file
$configuration = [];
if (file_exists($config_file)) {
    $config_file = realpath($config_file);
    stdOut("Found configuration file: " . $config_file);
    try {
        $configuration = require_once $config_file;
        if (!is_array($configuration)) throw new Exception("Not an array: " . $config_file);
    } catch (Throwable $e) {
        stdErr(PHP_EOL . "Configuration file unreadable: " . $e->getMessage());
        exit(1);
    }
} else {
    stdOut("Configuration file not found. Trying the build with sensible defaults.");
}

// doc root
$doc_root = $configuration["doc_root"] ?? __DIR__;
stdOut("doc_root         = " . $doc_root);
if (!file_exists($doc_root)) {
    stdErr("Doc root not found");
    exit(2);
}
$configuration["doc_root"] = $doc_root;

// vendor
$vendor_autoload = $configuration["vendor_autoload"] ?? dirname(__DIR__) . "/vendor/autoload.php";
stdOut("vendor_autoload  = " . $vendor_autoload);
if (!file_exists($vendor_autoload)) {
    stdErr("Vendor autoload not found or none given in configuration");
    exit(3);
}
require_once $vendor_autoload;
$configuration["vendor_autoload"] = $vendor_autoload;

// application root
$application_root = $configuration["application_root"] ?? dirname(__DIR__) . "/application";
if (!file_exists($application_root)) $application_root = dirname(__DIR__) . "/src ";
if (!file_exists($application_root)) {
    stdErr("Application root not found or none given in configuration");
    exit(4);
}
defined("APPLICATION_ROOT")
or define("APPLICATION_ROOT", realpath($application_root));

spl_autoload_register(function ($para) {
    $path = APPLICATION_ROOT . DIRECTORY_SEPARATOR
        . str_replace('\\', DIRECTORY_SEPARATOR, $para) . '.php';
    if (file_exists($path)) {
        include $path;
        return true;
    }
    return false;
});
stdOut("application_root = " . $application_root);
$configuration["application_root"] = $application_root;

// source directory
$source_directory = $configuration["source_directory"] ?? $application_root . "/bhenk";
if (!file_exists($source_directory)) {
    stdErr("Source directory not found or none given in configuration");
    exit(5);
}
stdOut("source_directory = " . $source_directory);
$configuration["source_directory"] = $source_directory;

$api_directory = $configuration["api_directory"] ?? $doc_root . "/api";
if (!file_exists($api_directory)) {
    mkdir($api_directory);
}
stdOut("api_directory    = " . $api_directory);
$configuration["api_directory"] = $api_directory;

use bhenk\doc2rst\conf\Config;
use bhenk\doc2rst\work\DocManager;
Config::load($configuration);
(new DocManager())->work();

exit(0);
