<?php

namespace bhenk\corewa\logging\build;

use bhenk\corewa\conf\Config;
use bhenk\corewa\util\Reflect;
use Exception;
use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\FormattableHandlerInterface;
use Monolog\Handler\Handler;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\ProcessorInterface;
use Psr\Log\LoggerInterface;
use Throwable;
use function array_keys;
use function count;
use function get_class;
use function is_null;

/**
 * Singleton class responsible for building loggers that implement {@link LoggerInterface}.
 *
 * <br/>
 * ###Configuration entry
 * This class expects a *configuration entry* in the general configuration file like this:
 * ```
 *"bhenk\corewa\logging\build\LoggerBuilder" => [
 *    "logger_definition_file" => "conf/logger_definition.php",
 *],
 * ```
 * whereby the key *logger_definition_file* points to the file path of the logger definitions.
 *
 * @see LoggerBuilder::LOGGER_DEFINITION_FILE
 * @see Config
 *
 */
class LoggerBuilder {

    /**
     * Key in the *configuration entry* of this class pointing to the absolute or relative path of the file
     * that contains logger definitions and/or creators, aka *logger definition file*.
     *
     * <br/>
     * ###Logger definition file
     *
     * The *logger definition file* contains an array of entries (type names) that point either to a *definition* or
     * the *creator* of a logger. The arrangement of a *logger definition file* goes like this:
     *
     * ```
     * return [
     *      "{type_name}" => ["definition" => [ {logger definition} ] ]
     *      ...
     *      "{type_name}" => ["creator" => [ {logger creator} ] ]
     *      ...
     * ];
     * ```
     * Order is not critical - entries with *definition*s and entries with *creator*s may be intermingled.
     *
     * <br/>
     * ###Logger definition
     *
     * Example of an entry in the *logger definition file* of a logger *definition*:
     * ```
     *"{entry 01}" => [
     *    "definition" => [
     *        "channel" => "{channel name}", // optional
     *        "handlers" => [
     *            "{handler01}" => [
     *                "class_name" => "{namespace\Handler}",
     *                "paras" => [  // optional
     *                    {name} => {value of constructor parameter}
     *                    ...
     *                ],
     *                "formatter" => [  // optional, Formatter for the above Handler
     *                    "class_name" => "{namespace\Formatter}",
     *                    "paras" => [ ]  // optional
     *                ],
     *            ],
     *            ...  // optional, more Handler definitions
     *        ],
     *        "processors" => [  // optional
     *            "{processor01}" => [
     *                "class_name" => "{namespace\Processor}",
     *                "paras" => [ ]  // optional
     *            ],
     *            ...  // optional, more Processor definitions
     *        ],
     *    ],
     *],
     * ```
     * In the above *{namespace\Classname}* is a placeholder for the fully qualified classname of a class
     * that implements the Monolog {@link HandlerInterface}, {@link FormatterInterface} or
     * {@link ProcessorInterface} respectively.
     *
     * <br/>
     * ###Logger creator
     *
     * Example of an entry in the *logger definition file* of a logger *creator*:
     * ```
     *"{entry02}" => [
     *    "creator" => [
     *        "class_name" => "{namespace\LoggerCreator}",
     *        "paras" => [ ]  // optional
     *    ],
     *],
     * ```
     * In the above *{namespace\LoggerCreator}* is a placeholder for the fully qualified classname of a class
     * that implements the {@link LoggerCreatorInterface}.
     *
     */
    public const LOGGER_DEFINITION_FILE = "logger_definition_file";
    private static ?LoggerBuilder $instance = null;
    private array $entries = [];
    private array $warnings = [];
    private bool $quiet = false;

    /**
     * Create a minimal logger with a {@link StreamHandler} set to *php://stderr*.
     *
     * @return Logger
     */
    public static function createDefaultErr(): Logger {
        $logger = new Logger("err");
        $logger->pushHandler(new StreamHandler('php://stderr', 100));
        return $logger;
    }

    /**
     * Create a minimal logger with a {@link StreamHandler} set to *php://stdout*.
     *
     * @return Logger
     */
    public static function createDefaultOut(): Logger {
        $logger = new Logger("out");
        $logger->pushHandler(new StreamHandler('php://stdout', 100));
        return $logger;
    }

    /**
     * Get the singleton instance of this class.
     *
     * @return LoggerBuilder
     */
    public static function get(): LoggerBuilder {
        if (is_null(self::$instance)) {
            self::$instance = new LoggerBuilder();
        }
        return self::$instance;
    }

    /**
     * Get a list of warnings issued during the previous call to {@link LoggerBuilder::build()}.
     *
     * @return array
     */
    public function getWarnings(): array {
        return $this->warnings;
    }

    /**
     * Do or do not print warnings to *php://stderr* during subsequent builds.
     *
     * @param bool $quiet
     * @return void
     */
    public function setQuiet(bool $quiet): void {
        $this->quiet = $quiet;
    }

    /**
     * Add an entry to the list of definitions and creators of this LoggerBuilder.
     *
     * Definition or creator should adhere to standards as found in the file pointed to by
     * {@link LoggerBuilder::LOGGER_DEFINITION_FILE}.
     *
     * As a side effect, this method may try to load the *logger definition file*.
     *
     * @param string $type name under which the logger defined or created will reside.
     * @param array $entry an array with a *definition* or *creator*
     * @return void
     * @throws Exception
     */
    public function addEntry(string $type, array $entry): void {
        $this->maybeLoadDefinitions();
        $this->entries[$type] = $entry;
    }

    /**
     * Empties the list of entries previously loaded.
     *
     * A subsequent call to {@link LoggerBuilder::build()} will
     * trigger a new attempt to load definitions and creators from the file pointed to by
     * {@link LoggerBuilder::LOGGER_DEFINITION_FILE}.
     *
     * @return void
     */
    public function reset(): void {
        $this->entries = [];
    }

    /**
     * Tries to build or create a certain type of logger.
     *
     * This method will look for a definition or creator of the given type as given by an entry in the *logger
     * definition file* pointed to by {@link LoggerBuilder::LOGGER_DEFINITION_FILE} or added by the method
     * {@link LoggerBuilder::addEntry()}.
     *
     * If not yet loaded this method will trigger an attempt to load the logger definition file.
     *
     * If anything should go wrong during build or creation of the requested logger,
     * this method guarantees to deliver at least a logger to *php://stdout*. In this case warnings will be printed
     * to *php://stderr*, unless this builder was requested to be quiet by a call to
     * {@link LoggerBuilder::setQuiet()}. A list of warnings issued during the build process can be obtained
     * by a call to {@link LoggerBuilder::getWarnings()}.
     *
     * @param string $type
     * @return LoggerInterface
     */
    public function build(string $type): LoggerInterface {
        $this->warnings = [];
        $logger = null;
        try {
            $this->maybeLoadDefinitions();
            $entry = $this->getEntry($type);
            $logger = $this->createLogger($type, $entry);
        } catch (Throwable $e) {
            $this->warnings[] = $e->getMessage()
                . " [" . get_class($e) . "]"
                . " (" . __METHOD__ . ":" . __LINE__ . ")";
        }
        if (count($this->warnings) > 0) {
            $this->warnings[] = "Unable to create logger with name '" . $type . "'";
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
            $logger_definition_file = $config[self::LOGGER_DEFINITION_FILE];
            $this->entries = require Config::get()->makeAbsolute($logger_definition_file);
        }
    }

    /**
     * @throws Exception
     */
    private function getEntry(string $type): array {
        if (!isset($this->entries[$type]))
            throw new Exception("Entry '" . $type . "' not found.");
        return $this->entries[$type];
    }

    /**
     * @throws Exception
     */
    private function createLogger(string $type, array $entry): LoggerInterface {
        if (empty($entry)) {
            throw new Exception("Could not create logger. Empty entry");
        }
        $key = array_keys($entry)[0];
        return match ($key) {
            "definition" => $this->createLoggerFromDefinition($type, $entry["definition"]),
            "creator" => $this->getLoggerFromCreator($type, $entry["creator"]),
            default => throw new Exception(
                "Unknown key: '" . $key . "'. "
                . "Expected either 'definition' or 'creator'."),
        };
    }

    /**
     * @throws Exception
     */
    private function createLoggerFromDefinition(string $type, array $definition): LoggerInterface {
        $channel = $definition["channel"] ?? $type;
        $logger = new Logger($channel);
        if (!isset($definition["handlers"])) {
            throw new Exception("No handlers set for logger '" . $type . "'");
        }
        $this->addHandlers($type, $logger, $definition["handlers"]);
        if (isset($definition["processors"])) $this->addProcessors($type, $logger, $definition["processors"]);
        return $logger;
    }

    /**
     * @throws Exception
     */
    private function addHandlers(string $type, Logger $logger, array $handlers): void {
        foreach ($handlers as $key => $handler) {
            if (!isset($handler["class_name"])) {
                throw new Exception("No 'class_name' set on handler '" . $key . "' from entry '" . $type . "'");
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
                $this->addFormatter($type, $object, $formatter);
            }
        }
    }

    /**
     * @throws Exception
     */
    private function addFormatter(string $type, FormattableHandlerInterface $handler, array $formatter): void {
        if (!isset($formatter["class_name"])) {
            throw new Exception(
                "No 'class_name' set on handler '" . get_class($handler) . "' from entry '" . $type . "'");
        }
        $class_name = $formatter["class_name"];
        $paras = $formatter["paras"] ?? [];
        $object = Reflect::createObject($class_name, $paras);
        $handler->setFormatter($object);
    }

    /**
     * @throws Exception
     */
    private function addProcessors(string $type, Logger $logger, array $processors): void {
        foreach ($processors as $key => $processor) {
            if (!isset($processor["class_name"])) {
                throw new Exception("No 'class_name' set on processor '" . $key . "' from entry '" . $type . "'");
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
    private function getLoggerFromCreator(string $type, array $creator): LoggerInterface {
        if (!isset($creator["class_name"])) {
            throw new Exception(
                "No 'class_name' set on creator under entry '" . $type . "'");
        }
        $class_name = $creator["class_name"];
        /** @var LoggerCreatorInterface $object */
        $object = Reflect::createObject($class_name);
        return $object->create($creator["paras"] ?? []);
    }
}