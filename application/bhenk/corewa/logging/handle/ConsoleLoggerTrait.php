<?php

namespace bhenk\corewa\logging\handle;

use bhenk\corewa\logging\Log;

trait ConsoleLoggerTrait {

    private string $previous;

    public static function setUpBeforeClass(): void {
        echo "hello " . static::class . "\n";
    }

    public function setUp(): void {
        $this->previous = Log::setType("stdout");
    }

    public function tearDown(): void {
        Log::setType($this->previous);
    }

    public static function tearDownAfterClass(): void {
        echo "goodbye " . static::class . "\n";
    }
}