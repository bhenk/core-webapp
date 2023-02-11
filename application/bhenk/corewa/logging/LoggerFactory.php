<?php

namespace bhenk\corewa\logging;

use bhenk\corewa\logging\build\LoggerBuilder;
use Psr\Log\LoggerInterface;
use function array_merge;

/**
 * Singleton responsible for holding a stock of classes that implement {@link LoggerInterface}.
 */
class LoggerFactory {

    private static ?LoggerFactory $instance = null;
    private array $loggers = [];
    private array $warnings = [];

    /**
     * Get the singleton instance of this class.
     *
     * @return LoggerFactory
     */
    public static function get(): LoggerFactory {
        if (self::$instance == null)
            self::$instance = new LoggerFactory();
        return self::$instance;
    }

    /**
     * Get the {@link LoggerInterface} indicated by {@link $type}. If the type of logger is not in stock
     * will delegate the construction of the wanted logger to {@link LoggerBuilder}.
     *
     * If anything goes wrong during construction of the wanted logger will return a default logger. In that
     * case warnings issued by {@link LoggerBuilder} can be requested by the method
     * {@link LoggerFactory::getWarnings()}.
     *
     * @param string $type
     * @return LoggerInterface
     */
    public function getLogger(string $type): LoggerInterface {
        if (!isset($this->loggers[$type])) {
            $builder = LoggerBuilder::get();
            $this->loggers[$type] = $builder->build($type);
            $this->warnings = array_merge($this->warnings, $builder->getWarnings());
        }
        return $this->loggers[$type];
    }

    /**
     * Get a list of all warnings issued by {@link LoggerBuilder} during construction of loggers.
     *
     * @return array
     */
    public function getWarnings(): array {
        return $this->warnings;
    }

    /**
     * Empties the warnings list.
     *
     * @return void
     */
    public function resetWarnings(): void {
        $this->warnings = [];
    }

    /**
     * Reset this LoggerFactory. Empties stock and warnings.
     *
     * @return void
     */
    public function reset(): void {
        $this->warnings = [];
        $this->loggers = [];
    }

    /**
     * Set or add the given logger under the given type to the stock of this LoggerFactory.
     *
     * @param string $type
     * @param LoggerInterface $logger
     * @return LoggerInterface|null the previous logger for the given type or null if the given type was not in
     * stock.
     */
    public function setLogger(string $type, LoggerInterface $logger): ?LoggerInterface {
        $previous = $this->loggers[$type] ?? null;
        $this->loggers[$type] = $logger;
        return $previous;
    }

}