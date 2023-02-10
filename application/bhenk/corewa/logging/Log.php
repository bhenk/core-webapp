<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

namespace bhenk\corewa\logging;

use Monolog\Level;
use Monolog\Logger;
use Stringable;
use function method_exists;

class Log {

    private static string $type = "default";

    public static function getType(): string {
        return self::$type;
    }

    public static function setType(string $type): string {
        $previous = self::$type;
        self::$type = $type;
        return $previous;
    }

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

    public static function emergency(Stringable|string $message, array $context = []): void {
        LoggerFactory::get()->getLogger(static::getType())->emergency($message, $context);
    }


    public static function alert(Stringable|string $message, array $context = []): void {
        LoggerFactory::get()->getLogger(static::getType())->alert($message, $context);
    }


    public static function critical(Stringable|string $message, array $context = []): void {
        LoggerFactory::get()->getLogger(static::getType())->critical($message, $context);
    }


    public static function error(Stringable|string $message, array $context = []): void {
        LoggerFactory::get()->getLogger(static::getType())->error($message, $context);
    }

    public static function warning(Stringable|string $message, array $context = []): void {
        LoggerFactory::get()->getLogger(static::getType())->warning($message, $context);
    }


    public static function notice(Stringable|string $message, array $context = []): void {
        LoggerFactory::get()->getLogger(static::getType())->notice($message, $context);
    }

    public static function info(Stringable|string $message, array $context = []): void {
        LoggerFactory::get()->getLogger(static::getType())->info($message, $context);
    }

    public static function debug(Stringable|string $message, array $context = []): void {
        LoggerFactory::get()->getLogger(static::getType())->debug($message, $context);
    }

    public static function log($level, Stringable|string $message, array $context = []): void {
        LoggerFactory::get()->getLogger(static::getType())->log($level, $message, $context);
    }

}