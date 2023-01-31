<?php

namespace unit\corewa\logging\build;

use bhenk\corewa\logging\build\AbstractLoggerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertInstanceOf;

class AbstractLoggerBuilderTest extends TestCase {

    public function testCreateDefaultOut() {
        $out = AbstractLoggerBuilder::createDefaultOut();
        assertInstanceOf(Logger::class, $out,
            "Expected a logger");

        $handler = $out->getHandlers()[0];
        assertInstanceOf(StreamHandler::class, $handler,
            "Expected a StreamHandler as first Handler");
    }

    public function testCreateDefaultErr() {
        $err = AbstractLoggerBuilder::createDefaultErr();
        assertInstanceOf(Logger::class, $err,
            "Expected a logger");

        $handler = $err->getHandlers()[0];
        assertInstanceOf(StreamHandler::class, $handler,
            "Expected a StreamHandler as first Handler");
    }

}
