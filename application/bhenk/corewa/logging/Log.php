<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

namespace bhenk\corewa\logging;

use Monolog\Level;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Stringable;
use function method_exists;

/**
 * Default log agent.
 *
 * The type of {@link LoggerInterface} used by this log agent defaults to "default".
 *
 * Documentation of logging calls copied from {@link LoggerInterface}.
 *
 * @see LoggerFactory::getLogger()
 */
class Log {

    private static string $type = "default";

    /**
     * Get the type of logger used by this log agent.
     *
     * @return string type currently in use.
     */
    public static function getType(): string {
        return self::$type;
    }

    /**
     * Set the type of logger used by this log agent.
     *
     * @param string $type the new type.
     * @return string the old type.
     */
    public static function setType(string $type): string {
        $previous = self::$type;
        self::$type = $type;
        return $previous;
    }

    /**
     * Set the level at which the handlers of this log agents logger will fire.
     *
     * **Warning:** do not use this method for logging configuration. Use a logger definition file as explained in
     * *bhenk\corewa\logging\build\LoggerBuilder* instead.
     *
     * Caveat: this method will only have effect on implementations of {@link LoggerInterface} that expose
     * the method *getHandlers()* and only on handlers that expose the method *setLevel()*.
     *
     * @param int|string|Level $level the level to set on the handlers of this log agent.
     * @return void
     */
    public static function setLevel(int|string|Level $level): void {
        $logger = LoggerFactory::get()->getLogger(static::getType());
        if ($logger instanceof Logger) {
            foreach ($logger->getHandlers() as $handler) {
                if (method_exists($handler, "setLevel")) {
                    $handler->setLevel($level);
                }
            }
        }
    }

    /**
     * System is unusable.
     *
     * @param Stringable|string $message
     * @param array $context
     * @return void
     */
    public static function emergency(Stringable|string $message, array $context = []): void {
        LoggerFactory::get()->getLogger(static::getType())->emergency($message, $context);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param Stringable|string $message
     * @param array $context
     * @return void
     */
    public static function alert(Stringable|string $message, array $context = []): void {
        LoggerFactory::get()->getLogger(static::getType())->alert($message, $context);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param Stringable|string $message
     * @param array $context
     * @return void
     */
    public static function critical(Stringable|string $message, array $context = []): void {
        LoggerFactory::get()->getLogger(static::getType())->critical($message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param Stringable|string $message
     * @param array $context
     * @return void
     */
    public static function error(Stringable|string $message, array $context = []): void {
        LoggerFactory::get()->getLogger(static::getType())->error($message, $context);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param Stringable|string $message
     * @param array $context
     * @return void
     */
    public static function warning(Stringable|string $message, array $context = []): void {
        LoggerFactory::get()->getLogger(static::getType())->warning($message, $context);
    }

    /**
     * Normal but significant events.
     *
     * @param Stringable|string $message
     * @param array $context
     * @return void
     */
    public static function notice(Stringable|string $message, array $context = []): void {
        LoggerFactory::get()->getLogger(static::getType())->notice($message, $context);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param Stringable|string $message
     * @param array $context
     * @return void
     */
    public static function info(Stringable|string $message, array $context = []): void {
        LoggerFactory::get()->getLogger(static::getType())->info($message, $context);
    }

    /**
     * Detailed debug information.
     *
     * @param Stringable|string $message
     * @param array $context
     * @return void
     */
    public static function debug(Stringable|string $message, array $context = []): void {
        LoggerFactory::get()->getLogger(static::getType())->debug($message, $context);
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param int|Level|string $level
     * @param Stringable|string $message
     * @param array $context
     * @return void
     */
    public static function log(int|string|Level $level, Stringable|string $message, array $context = []): void {
        LoggerFactory::get()->getLogger(static::getType())->log($level, $message, $context);
    }

}