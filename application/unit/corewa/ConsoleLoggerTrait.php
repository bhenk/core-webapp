<?php

namespace unit\corewa;

use bhenk\corewa\logging\Log;
use function get_class;

trait ConsoleLoggerTrait {

    private string $previous;

    public function setUp(): void {
        echo "hello " . get_class($this) . "\n";
        $this->previous = Log::setType("stdout");
    }

    public function tearDown(): void {
        //echo "goodbye " . get_class($this) . "\n";
        Log::setType($this->previous);
    }
}