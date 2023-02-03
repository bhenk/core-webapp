<?php

namespace bhenk\corewa\logging\build;

use bhenk\corewa\conf\Config;
use bhenk\corewa\util\Path;
use bhenk\corewa\util\Reflect;
use Exception;
use Monolog\Handler\FormattableHandlerInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Throwable;
use function array_keys;
use function array_values;
use function count;
use function get_class;
use function is_null;

class LoggerBuilder {

    private static ?LoggerBuilder $instance = null;
    private array $entries = [];
    private array $warnings = [];
    private bool $quiet = false;

    public static function createDefaultErr(): Logger {
        $logger = new Logger("err");
        $logger->pushHandler(new StreamHandler('php://stderr', 100));
        return $logger;
    }

    public static function createDefaultOut(): Logger {
        $logger = new Logger("out");
        $logger->pushHandler(new StreamHandler('php://stdout', 100));
        return $logger;
    }

    public static function get(): LoggerBuilder {
        if (is_null(self::$instance)) {
            self::$instance = new LoggerBuilder();
        }
        return self::$instance;
    }

    /**
     * @return array
     */
    public function getWarnings(): array {
        return $this->warnings;
    }

    public function setQuiet(bool $quiet): void {
        $this->quiet = $quiet;
    }

    /**
     * @throws Exception
     */
    public function addEntry(string $name, array $entry): void {
        $this->maybeLoadDefinitions();
        $this->entries[$name] = $entry;
    }

    public function reset(): void {
        $this->entries = [];
    }

    public function build(string $name): LoggerInterface {
        $this->warnings = [];
        $logger = null;
        try {
            $this->maybeLoadDefinitions();
            $entry = $this->getEntry($name);
            $logger = $this->createLogger($name, $entry);
        } catch (Throwable $e) {
            $this->warnings[] = $e->getMessage()
                . " [" . get_class($e) . "]"
                . " (" . __METHOD__ . ":" . __LINE__ . ")";
        }
        if (count($this->warnings) > 0) {
            $this->warnings[] = "Unable to create logger with name '" . $name . "'";
            $logger = self::createDefaultOut();
            $this->warnings[] =
                "Could not create logger. See above for details. Using fallback logger.";
            if (!$this->quiet) {
                $err = self::createDefaultErr();
                foreach ($this->warnings as $warning) {
                    $err->error($warning);
                }
            }
        }
        return $logger;
    }

    /**
     * @throws Exception
     */
    private function maybeLoadDefinitions(): void {
        if (empty($this->entries)) {
            $config = Config::get()->getConfigurationFor(get_class($this));
            $logger_definition_file = $config["logger_definition_file"];
            $this->entries = require Config::get()->makeAbsolute($logger_definition_file);
        }
    }

    /**
     * @throws Exception
     */
    private function getEntry(string $name): array {
        if (!isset($this->entries[$name]))
            throw new Exception("Entry '" . $name . "' not found.");
        return $this->entries[$name];
    }

    /**
     * @throws Exception
     */
    private function createLogger(string $name, array $entry): LoggerInterface {
        if (empty($entry)) {
            throw new Exception("Could not create logger. Empty entry");
        }
        $key = array_keys($entry)[0];
        return match ($key) {
            "definition" => $this->createLoggerFromDefinition($name, $entry["definition"]),
            "creator" => $this->getLoggerFromCreator($name, $entry["creator"]),
            default => throw new Exception(
                "Unknown key: '" . $key . "'. "
                . "Expected either 'definition' or 'creator'."),
        };
    }

    /**
     * @throws Exception
     */
    private function createLoggerFromDefinition(string $name, array $definition): LoggerInterface {
        $channel = $definition["channel"] ?? $name;
        $logger = new Logger($channel);
        if (!isset($definition["handlers"])) {
            throw new Exception("No handlers set for logger '" . $name . "'");
        }
        $this->addHandlers($name, $logger, $definition["handlers"]);
        if (isset($definition["processors"])) $this->addProcessors($name, $logger, $definition["processors"]);
        return $logger;
    }

    /**
     * @throws Exception
     */
    private function addHandlers(string $name, Logger $logger, array $handlers): void {
        foreach ($handlers as $key => $handler) {
            if (!isset($handler["class_name"])) {
                throw new Exception("No 'class_name' set on handler '" . $key . "' from entry '" . $name . "'");
            }
            $class_name = $handler["class_name"];
            $paras = $handler["paras"] ?? [];
            if (isset($paras["filename"])) {
                $paras["filename"] = Config::get()->makeAbsolute($paras["filename"], false);
            }
            $object = Reflect::createObject($class_name, $paras);
            $logger->pushHandler($object);

            $formatter = $handler["formatter"] ?? false;
            if ($object instanceof FormattableHandlerInterface and $formatter) {
                $this->addFormatter($name, $object, $formatter);
            }
        }
    }

    /**
     * @throws Exception
     */
    private function addFormatter(string $name, FormattableHandlerInterface $handler, array $formatter): void {
        if (!isset($formatter["class_name"])) {
            throw new Exception(
                "No 'class_name' set on handler '" . get_class($handler) . "' from entry '" . $name . "'");
        }
        $class_name = $formatter["class_name"];
        $paras = $formatter["paras"] ?? [];
        $object = Reflect::createObject($class_name, $paras);
        $handler->setFormatter($object);
    }

    /**
     * @throws Exception
     */
    private function addProcessors(string $name, Logger $logger, array $processors): void {
        foreach ($processors as $key => $processor) {
            if (!isset($processor["class_name"])) {
                throw new Exception("No 'class_name' set on processor '" . $key . "' from entry '" . $name . "'");
            }
            $class_name = $processor["class_name"];
            $paras = $processor["paras"] ?? [];
            $object = Reflect::createObject($class_name, $paras);
            $logger->pushProcessor($object);
        }
    }

    /**
     * @throws Exception
     */
    private function getLoggerFromCreator(string $name, array $creator): LoggerInterface {
        if (!isset($creator["class_name"])) {
            throw new Exception(
                "No 'class_name' set on creator under entry '" . $name . "'");
        }
        $class_name = $creator["class_name"];
        /** @var LoggerCreatorInterface $object */
        $object = Reflect::createObject($class_name);
        $paras = $creator["paras"] ?? [];
        $v = array_values($paras);
        return match (count($paras)) {
            0 => $object->create(),
            1 => $object->create($v[0]),
            2 => $object->create($v[0], $v[1]),
            3 => $object->create($v[0], $v[1], $v[2]),
            4 => $object->create($v[0], $v[1], $v[2], $v[3]),
            5 => $object->create($v[0], $v[1], $v[2], $v[3], $v[4]),
            default => throw new Exception("Too many parameters in creator '" . $name . "'"),
        };
    }
}