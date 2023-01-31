<?php

namespace bhenk\corewa\logging;

use bhenk\corewa\logging\build\DefaultLoggerBuilder;
use bhenk\corewa\logging\build\ErrLoggerBuilder;
use bhenk\corewa\logging\build\OutLoggerBuilder;
use Psr\Log\LoggerInterface;

class LoggerFactory {

    private static ?LoggerFactory $instance = null;
    private array $loggers = [];
    private array $warnings = [];

    public static function get(): LoggerFactory {
        if (self::$instance == null)
            self::$instance = new LoggerFactory();
        return self::$instance;
    }

    public function getLogger(Type $type): LoggerInterface {
        if (!isset($this->loggers[$type->name])) {
            $builder = null;
            switch ($type) {
                case Type::default :
                    $builder = new DefaultLoggerBuilder();
                    break;
                case Type::stdout :
                    $builder = new OutLoggerBuilder();
                    break;
                case Type::stderr :
                    $builder = new ErrLoggerBuilder();
                    break;
            }
            $this->loggers[$type->name] = $builder->buildLogger();
            $this->warnings += $builder->getWarnings();
        }
        return $this->loggers[$type->name];
    }

    /**
     * @return array
     */
    public function getWarnings(): array {
        return $this->warnings;
    }


}