<?php

namespace bhenk\corewa\conf;

use Exception;
use function explode;
use function get_class;

class Version {

    private const UNKNOWN = "unknown";
    private const VERSION = "version";
    private const APPLICATION = "application";
    private const DATE = "date";

    private string $application;

    private string $version;
    private string $date;

    function __construct() {
        try {
            $config = Config::get()->getConfigurationFor(get_class($this));
            $this->version = $config[self::VERSION] ?? self::UNKNOWN;
            $this->application = $config[self::APPLICATION] ?? self::UNKNOWN;
            $this->date = $config[self::DATE] ?? self::UNKNOWN;
        } catch (Exception) {
            $this->application = self::UNKNOWN;
            $this->version = self::UNKNOWN;
            $this->date = self::UNKNOWN;
        }
    }

    /**
     * @return string
     */
    public function info(): string {
        $s = ($this->application == self::UNKNOWN) ? "application unknown" : $this->application;
        $d = ($this->date == self::UNKNOWN) ? "" : " (" . $this->date . ")";
        return $s . " version " . $this->version . $d;
    }

    /**
     * @return string
     */
    public function getApplication(): string {
        return $this->application;
    }

    /**
     * @return string
     */
    public function getVersion(): string {
        return $this->version;
    }

    /**
     * @return string
     */
    public function getMajorVersion(): string {
        if ($this->version == self::UNKNOWN) return $this->version;
        $v = explode(".", $this->version);
        return $v[0] ?? self::UNKNOWN;
    }

    /**
     * @return string
     */
    public function getMinorVersion(): string {
        if ($this->version == self::UNKNOWN) return $this->version;
        $v = explode(".", $this->version);
        return $v[1] ?? self::UNKNOWN;
    }

    /**
     * @return string
     */
    public function getPatch(): string {
        if ($this->version == self::UNKNOWN) return $this->version;
        $v = explode(".", $this->version);
        return $v[2] ?? self::UNKNOWN;
    }

    /**
     * @return string
     */
    public function getDate(): string {
        return $this->date;
    }

}