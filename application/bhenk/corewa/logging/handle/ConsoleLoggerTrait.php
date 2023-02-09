<?php

namespace bhenk\corewa\logging\handle;

use bhenk\corewa\logging\Log;
use function print_r;
use function str_pad;

trait ConsoleLoggerTrait {

    private string $previous;

    private static function is_trait_on(): bool {
        return static::$console_logger_trait_on ?? true;
    }

    public static function setUpBeforeClass(): void {
        if (self::is_trait_on()) {
            echo PHP_EOL . "hello " . static::class . PHP_EOL;
        }
        parent::setUpBeforeClass();
    }

    public function setUp(): void {
        if (self::is_trait_on()) {
            $this->previous = Log::setType("console_logger");
            print_r(str_pad(parent::getName(), 120, "-"));
        }
        parent::setUp();
    }

    public function tearDown(): void {
        if (self::is_trait_on()) {
            Log::setType($this->previous);
        }
        parent::tearDown();
    }

    public static function tearDownAfterClass(): void {
        if (self::is_trait_on()) {
            echo "goodbye " . static::class . PHP_EOL;
        }
        parent::tearDownAfterClass();
    }
}