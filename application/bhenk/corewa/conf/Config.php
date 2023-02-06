<?php

namespace bhenk\corewa\conf;

use Exception;
use InvalidArgumentException;
use function file_exists;
use function is_null;

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
        $this->config = require self::absolute($config_file, $application_root);
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

    /**
     * @throws Exception
     */
    private static function absolute(string $path, ?string $application_root, bool $must_exist = true): string {
        if ($path == "" or $path == "/")
            throw new InvalidArgumentException("Argument cannot be empty string: \$path : '" . $path . "'");
        if (!str_starts_with($path, DIRECTORY_SEPARATOR)) {
            $path = $application_root . DIRECTORY_SEPARATOR . $path;
        }
        if (!file_exists($path) and $must_exist)
            throw new Exception("File does not exists: '" . $path . "'");
        return $path;
    }

    public function getApplicationRoot(): string {
        return $this->application_root;
    }

    /**
     * @throws Exception
     */
    public function makeAbsolute(string $path, bool $must_exist = true): string {
        return self::absolute($path, $this->application_root, $must_exist);
    }

    public function getConfigFile(): string {
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