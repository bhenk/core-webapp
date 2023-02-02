<?php

namespace unit\corewa\logging\build;

use bhenk\corewa\logging\build\LoggerCreatorInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class DummyCreator implements LoggerCreatorInterface {

    private static bool $is_called = false;
    private static array $paras = [];

    function create(...$paras): Logger {
        foreach ($paras as $para) {
            self::$paras[] = $para;
        }
        self::$is_called = true;
        $logger = new Logger("dummy");
        $logger->pushHandler(new StreamHandler('php://stdout', 100));
        return $logger;
    }

    public static function getParas(): array {
        return self::$paras;
    }

    public static function wasCalled(): bool {
        return self::$is_called;
    }

    public static function reset() {
        self::$is_called = false;
        self::$paras = [];
    }
}