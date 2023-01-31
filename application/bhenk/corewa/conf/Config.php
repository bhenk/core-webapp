<?php

namespace bhenk\corewa\conf;

use Exception;

class Config {

    private static ?Config $instance = null;
    private array $config;

    /**
     * @throws Exception
     */
    private function __construct(string $config_file) {
        $this->config = require $config_file;
    }

    /**
     * @throws Exception
     */
    public static function load(string $config_file): Config {
        if (!file_exists($config_file)) throw new Exception("File does not exist: " . $config_file);
        self::$instance = new Config($config_file);
        return self::$instance;
    }

    public static function reset(): ?Config {
        $previous = self::$instance;
        self::$instance = null;
        return $previous;
    }

    /**
     * @throws Exception
     */
    public static function get(): Config {
        if (is_null(self::$instance)) {
            throw new Exception(
                "Instance not loaded. Call " . Config::class . "::load(string \$file) before ::get()");
        }
        return self::$instance;
    }

    /**
     * @return array
     */
    public function getConfig(): array {
        return $this->config;
    }

    /**
     * @param array $config
     */
    public function setConfig(array $config): void {
        $this->config = $config;
    }

    /**
     * @throws Exception
     */
    public function getConfigurationFor(string $name): array {
        if (!isset($this->config[$name])) throw new Exception("Configuration '" . $name . "' not set or null");
        return $this->config[$name];
    }

    public function setConfigurationFor(string $name, array $configuration): ?array {
        $previous = $this->config[$name] ?? null;
        $this->config[$name] = $configuration;
        return $previous;
    }
}