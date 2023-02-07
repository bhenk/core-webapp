<?php

namespace bhenk\corewa\store\sql;

use bhenk\corewa\conf\Config;
use bhenk\corewa\logging\Log;
use Exception;
use mysqli;
use function get_class;

class MysqlConnector {

    private static ?MysqlConnector $instance = null;

    private mysqli $mysqli;

    /**
     * @throws Exception
     */
    function __construct() {
        $this->getConnector();
    }

    public static function get(): MysqlConnector {
        if (self::$instance == null)
            self::$instance = new MysqlConnector();
        return self::$instance;
    }

    public static function closeConnection(): void {
        if (!self::$instance == null) {
            if (isset(self::$instance->mysqli)) {
                self::$instance->mysqli->close();
                unset(self::$instance->mysqli);
                Log::info("Closed connection to mysql database");
            }
        }
    }

    /**
     * @throws Exception
     */
    public function getConnector(): mysqli {
        if (!isset($this->mysqli)) {
            $config = Config::get()->getConfigurationFor(get_class($this));
            $this->validateConfig($config);
            $persistent = $config["persistent"] ?? true;
            $hostname = $persistent ? "p:" . $config["hostname"] : $config["hostname"];
            $port = $config["port"] ?? 3306;
            try {
                $this->mysqli = new mysqli($hostname, $config["username"],
                    $config["password"], $config["database"], $port);
                Log::info("Created connection to mysql database '" . $config["database"] . "'");
            } catch (Exception $e) {
                Log::error("Could not create connection to mysql database '"
                    . $config["database"] . "'", [$e]);
                throw $e;
            }
        }
        return $this->mysqli;
    }

    /**
     * @throws Exception
     */
    private function validateConfig(array $config): void {
        $args = ["hostname", "username", "password", "database"];
        foreach ($args as $arg) {
            if (!isset($config[$arg])) {
                $msg = "'$arg' not found in configuration for " . get_class($this) . " in configuration "
                    . Config::get()->getConfigFile();
                Log::error($msg);
                throw new Exception($msg);
            }
        }
    }

}