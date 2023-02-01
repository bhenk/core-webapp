<?php

namespace bhenk\corewa\logging;

use bhenk\corewa\logging\build\AbstractLoggerBuilder;
use bhenk\corewa\logging\build\DefaultLoggerBuilder;
use bhenk\corewa\logging\build\ErrLoggerBuilder;
use bhenk\corewa\logging\build\LoggerBuilderInterface;
use bhenk\corewa\logging\build\OutLoggerBuilder;
use Exception;
use Monolog\Logger;
use PHPUnit\Framework\MockObject\IncompatibleReturnValueException;
use Psr\Log\LoggerInterface;
use function array_merge;
use function count;
use function var_dump;

class LoggerFactory {

    private static ?LoggerFactory $instance = null;
    private array $loggers = [];
    private array $customLoggers = [];
    private array $warnings = [];

    private bool $quiet = true;

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
            $this->warnings = array_merge($this->warnings, $builder->getWarnings());
        }
        return $this->loggers[$type->name];
    }

    public function getCustomLogger(string $name, LoggerBuilderInterface $builder): Logger {
        $custom_warnings = [];
        if (!isset($this->customLoggers[$name])) {
            try {
                $this->customLoggers[$name] = $builder->buildLogger();
            } catch (Exception $e) {
                $custom_warnings[] = $e->getMessage();
                $custom_warnings[] = "Could not build custom logger '".$name."'. See above for details.";
                $this->customLoggers[$name] = AbstractLoggerBuilder::createDefaultOut();
            }
            $custom_warnings = array_merge($builder->getWarnings(), $custom_warnings);
            if (!empty($custom_warnings && !$this->quiet)) {
                $err = AbstractLoggerBuilder::createDefaultErr();
                foreach ($custom_warnings as $warning) {
                    $err->error($warning);
                }
            }
            $this->warnings = array_merge($this->warnings, $custom_warnings);
        }
        return $this->customLoggers[$name];
    }

    /**
     * @return array
     */
    public function getWarnings(): array {
        return $this->warnings;
    }

    public function reset() :void {
        $this->warnings = [];
        $this->loggers = [];
        $this->customLoggers = [];
    }

    /**
     * @return bool
     */
    public function isQuiet(): bool {
        return $this->quiet;
    }

    /**
     * @param bool $quiet
     */
    public function setQuiet(bool $quiet): void {
        $this->quiet = $quiet;
    }

}