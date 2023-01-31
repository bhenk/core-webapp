<?php

namespace bhenk\corewa\logging\build;

use Monolog\Logger;

class ErrLoggerBuilder extends StreamLoggerBuilder {

    protected function getStreamName(): string {
        return "php://stderr";
    }

    protected function createFallBackLogger(): Logger {
        return AbstractLoggerBuilder::createDefaultErr();
    }

    protected function getChannel(): string {
        return "err";
    }
}