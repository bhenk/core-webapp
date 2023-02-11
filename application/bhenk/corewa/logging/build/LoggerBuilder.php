<?php

namespace bhenk\corewa\logging\build;

use bhenk\corewa\conf\Config;
use bhenk\corewa\util\Reflect;
use Exception;
use Monolog\Handler\FormattableHandlerInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Throwable;
use function array_keys;
use function count;
use function get_class;
use function is_null;

/**
 * Singleton class responsible for building loggers that implement {@link LoggerInterface}.
 *
 * LoggerBuilder will get a list of *definitions* and *creators* of loggers from
 * the {@link LoggerBuilder::LOGGER_DEFINITION_FILE} as pointed to in the configuration of this class by
 * {@link Config::getConfigurationFor()}. Loggers will only be build after a request to
 * {@link LoggerBuilder::build()}.
 *
 */
class LoggerBuilder {

    /**
     * Key pointing to the absolute or relative (relative to application root) path of the file that contains
     * logger definitions and/or creators.
     * <br/><br/>
     * ##Configuration file
     * The configuration file is read by the singleton {@link Config}. For this {@link LoggerBuilder} the
     * configuration entry should look something like
     * ```
     *"bhenk\corewa\logging\build\LoggerBuilder" => [
     *    "logger_definition_file" => "conf/logger_definition.php",
     *],
     * ```
     * <br/><br/>
     * ##Logger definition file
     *
     * The logger definition file should give an array of logger types and is used by this class by means of
     * a *require* statement.
     * <br/><br/>
     * ###Logger definition
     *
     * Example of an entry in the logger definition file of a logger definition:
     * ```
     *"stderr" => [
     *    "definition" => [
     *        "channel" => "err",
     *        "handlers" => [
     *            "handler01" => [
     *                "class_name" => "Monolog\Handler\StreamHandler",
     *                "paras" => [
     *                    "stream" => "php://stderr",
     *                    "level" => Level::Error,
     *                    "bubble" => true,
     *                    "filePermission" => null,
     *                    "useLocking" => false,
     *                ],
     *                "formatter" => [
     *                    "class_name" => "Monolog\Formatter\LineFormatter",
     *                    "paras" => [
     *                        "format" => "%level_name% | %datetime% > %message% | %context% %extra%\n",
     *                        "dateFormat" => "H:i:s:u",
     *                        "allowInlineLineBreaks" => true,
     *                        "ignoreEmptyContextAndExtra" => false,
     *                        "includeStacktraces" => true
     *                    ],
     *                ],
     *            ],
     *        ],
     *        "processors" => [
     *            "processor01" => [
     *                "class_name" => "Monolog\Processor\IntrospectionProcessor",
     *                "paras" => [
     *                    "level" => Level::Debug,
     *                    "skipClassesPartials" => [],
     *                    "skipStackFramesCount" => 1,
     *                ],
     *            ],
     *        ],
     *    ],
     *],
     * ```
     * <br/><br/>
     * ###Logger creator
     *
     * Example of an entry in the logger definition file of a logger creator:
     *
     * ```
     *"req" => [
     *    "creator" => [
     *        "class_name" => "bhenk\corewa\logging\build\RequestLoggerCreator",
     *        "paras" => [
     *            "level" => Level::Info,
     *            "filename" => "logs/req/req.log",
     *            "max_files" => 10,
     *            "filename_format" => "{filename}-{date}",
     *            "filename_date_format" => "Y-m",
     *            "format" => "%datetime% %extra%\n"
     *        ]
     *    ],
     *],
     *```
     *
     * @see Config
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
     * As a side effect this method may try to load the definition file.
     *
     * @param string $type name under which the logger defined or created will reside.
     * @param array $entry an array with a definition or creator
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
     * This method will look for a definition or creator of the given type as given by an entry in the logger
     * definition file pointed to by {@link LoggerBuilder::LOGGER_DEFINITION_FILE} or added by the method
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
        return $object->create($creator["paras"] ?? []);
    }
}