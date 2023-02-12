<?php

namespace bhenk\corewa\conf;

use Exception;
use function file_exists;
use function is_null;

/**
 * Singleton class responsible for reading configuration.
 *
 * <br/>
 * Classes that need configuration can have a *configuration entry* in the *general configuration file* read by this
 * class.
 *
 * The *general configuration file* has the format
 * ```
 * return [
 *      "{namespace\Classname}" => [
 *              {whatever configuration knowledge the class needs}
 *      ],
 *      ...
 * ]
 * ```
 */
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
        $this->config_file = self::absolute($config_file, $application_root);
        $this->config = require $this->config_file;
    }

    /**
     * Loads the configuration file.
     *
     * This static method should be called before a call to {@link Config::get()}.
     *
     * @param string $application_root application root, parent directory of public_html
     * @param string $config_file absolute or relative (relative to application root) path to the configuration file.
     * @return Config
     * @throws Exception the configuration file could not be read
     */
    public static function load(string $application_root, string $config_file): Config {
        self::$instance = new Config($application_root, $config_file);
        return self::$instance;
    }

    /**
     * Gets the singleton instance of this class.
     *
     * @throws Exception {@link Config::get()} was called before {@link Config::load()}
     */
    public static function get(): Config {
        if (is_null(self::$instance)) {
            throw new Exception(
                "Instance not loaded. Call " . Config::class . "::load(string \$file) before ::get()");
        }
        return self::$instance;
    }

    /**
     * Reset a possibly loaded instance of this class.
     *
     * After this, a call to {@link Config::load()} should be made prior to a call to {@link Config::get()}.
     *
     * @return Config|null the previous instance
     */
    public static function reset(): ?Config {
        $previous = self::$instance;
        self::$instance = null;
        return $previous;
    }

    /**
     * Get the *application root*, parent directory of public_html.
     *
     * @return string
     */
    public function getApplicationRoot(): string {
        return $this->application_root;
    }

    /**
     * Make given path absolute.
     *
     * Path may not be "" or "/". An {@link Exception} will be thrown in this case.
     *
     * An {@link Exception} will also be thrown if the parameter {@link $must_exist} is set to *true* and the
     * designated path does not exist.
     *
     * @param string $path the path to make absolute
     * @param bool $must_exist the file must exist or not, default true
     * @return string the absolute path
     * @throws Exception
     */
    public function makeAbsolute(string $path, bool $must_exist = true): string {
        return self::absolute($path, $this->application_root, $must_exist);
    }

    /**
     * Get the *general configuration file* used for loading this Config.
     *
     * @return string absolute path to configuration file
     */
    public function getConfigFile(): string {
        return $this->config_file;
    }

    /**
     * Get the configuration loaded by this Config.
     *
     * @return array
     */
    public function getConfig(): array {
        return $this->config;
    }

    /**
     * Set the configuration for this Config.
     *
     * Erases the loaded configuration.
     *
     * @param array $config
     */
    public function setConfig(array $config): void {
        $this->config = $config;
    }

    /**
     * Get the configuration for the given name.
     *
     * Usually the {@link $name} is the *namespace\Classname* of the class that needs configuration.
     *
     * @param string $name
     * @return array
     * @throws Exception if the given $name was not set in this configuration
     */
    public function getConfigurationFor(string $name): array {
        if (!isset($this->config[$name])) throw new Exception("Configuration '" . $name . "' not set or null");
        return $this->config[$name];
    }

    /**
     * Set the configuration for the given $name.
     *
     * @param string $name
     * @param array $configuration
     * @return array|null the previous configuration for $name or null if $name was not defined
     */
    public function setConfigurationFor(string $name, array $configuration): ?array {
        $previous = $this->config[$name] ?? null;
        $this->config[$name] = $configuration;
        return $previous;
    }

    /**
     * @throws Exception
     */
    private static function absolute(string $path, ?string $application_root, bool $must_exist = true): string {
        if ($path == "" or $path == "/")
            throw new Exception("Argument cannot be empty string: \$path : '" . $path . "'");
        if (!str_starts_with($path, DIRECTORY_SEPARATOR)) {
            $path = $application_root . DIRECTORY_SEPARATOR . $path;
        }
        if (!file_exists($path) and $must_exist)
            throw new Exception("File does not exists: '" . $path . "'");
        return $path;
    }
}