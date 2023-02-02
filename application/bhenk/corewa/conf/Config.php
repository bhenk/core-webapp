<?php

namespace bhenk\corewa\conf;

use bhenk\corewa\util\Path;
use Exception;

class Config {

    private static ?Config $instance = null;
    private string $application_root;
    private string $config_file;
    private array $config;

    /**
     * @throws Exception
     */
    private function __construct(string $application_root, string $config_file) {
        $this->application_root = $application_root;
        $this->config_file = $config_file;
        $this->config = require Path::makeAbsolute($config_file, $application_root);
    }

    /**
     * @throws Exception
     */
    public static function load(string $application_root, string $config_file): Config {
        self::$instance = new Config($application_root, $config_file);
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

    public function getApplicationRoot(): string {
        return $this->application_root;
    }

    public function getConfigFile() {
        return $this->config_file;
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