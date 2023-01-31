<?php

namespace bhenk\corewa\logging\build;

use Monolog\Logger;

class OutLoggerBuilder extends StreamLoggerBuilder {

    protected function createFallBackLogger(): Logger {
        return self::createDefaultOut();
    }

    protected function getChannel(): string {
        return "out";
    }

    protected function getStreamName(): string {
        return "php://stdout";
    }
}