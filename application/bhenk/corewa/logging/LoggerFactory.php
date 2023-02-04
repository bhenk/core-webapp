<?php

namespace bhenk\corewa\logging;

use bhenk\corewa\logging\build\LoggerBuilder;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use function array_merge;

class LoggerFactory {

    private static ?LoggerFactory $instance = null;
    private array $loggers = [];

    private array $warnings = [];

    public static function get(): LoggerFactory {
        if (self::$instance == null)
            self::$instance = new LoggerFactory();
        return self::$instance;
    }

    public function getLogger(string $name): LoggerInterface {
        if (!isset($this->loggers[$name])) {
            $builder = LoggerBuilder::get();
            $this->loggers[$name] = $builder->build($name);
            $this->warnings = array_merge($this->warnings, $builder->getWarnings());
        }
        return $this->loggers[$name];
    }

    /**
     * @return array
     */
    public function getWarnings(): array {
        return $this->warnings;
    }

    public function reset(): void {
        $this->warnings = [];
        $this->loggers = [];
    }

    public function setLogger(string $name, Logger $logger): void {
        $this->loggers[$name] = $logger;
    }

}