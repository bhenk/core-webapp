<?php

namespace bhenk\corewa\logging\build;

use bhenk\corewa\conf\Config;
use Exception;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\FormattableHandlerInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

abstract class AbstractLoggerBuilder implements LoggerBuilderInterface {

    const CHANNEL = "channel";
    const LOG_FILE = "log_file";
    const ERR_FILE = "err_file";
    const LOG_LEVEL = "log_level";
    const ERR_LEVEL = "err_level";
    const MAX_LOG_FILES = "max_log_files";
    const MAX_ERR_FILES = "max_err_files";
    const LINE_FORMAT = "line_format";
    const DATE_FORMAT = "date_format";

    protected array $warnings = [];
    protected bool $quiet = false;
    private array $config = [];

    function __construct(?array $config = []) {
        $this->config = $config;
    }

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

    public abstract function buildLogger(): Logger;

    /**
     * @throws Exception
     */
    public function getConfig(): array {
        if (empty($this->config)) $this->config = Config::get()->getConfigurationFor(get_class($this));
        return $this->config;
    }

    public function setConfig(array $config): void {
        $this->config = $config;
    }

    public function getWarnings(): array {
        return $this->warnings;
    }


    public function isQuiet(): bool {
        return $this->quiet;
    }

    public function setQuiet(bool $quiet): void {
        $this->quiet = $quiet;
    }

    protected abstract function createFallBackLogger(): Logger;

    protected function addLineFormatter(array $config, FormattableHandlerInterface $handler): void {
        if (isset($config[self::LINE_FORMAT])) {
            $format = $config[self::LINE_FORMAT];
            $date_format = $config[self::DATE_FORMAT] ?? null;
            $handler->setFormatter(new LineFormatter($format, $date_format));
        }
    }

    protected function checkWarnings(?Logger $logger): Logger {
        if (is_null($logger)) {
            $this->warnings[] = "Unable to create logger";
        }
        if (count($this->warnings) > 0) {
            $this->warnings[] =
                "Could not create custom logger. See above for details. Using fallback logger.";
            if (!$this->quiet) {
                $err = self::createDefaultErr();
                foreach ($this->warnings as $warning) {
                    $err->error($warning);
                }
            }
            $logger = $this->createFallBackLogger();
        }
        return $logger;

    }

}