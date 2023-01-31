<?php

namespace corewa\logging\build;

use bhenk\corewa\logging\build\AbstractLoggerBuilder;
use bhenk\corewa\logging\build\OutLoggerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertEmpty;
use function PHPUnit\Framework\assertInstanceOf;
use function PHPUnit\Framework\assertNotEmpty;

class OutLoggerBuilderTest extends TestCase {

    public function testBuildLogger() {
        $normal_config = [
            AbstractLoggerBuilder::CHANNEL => "out",
            AbstractLoggerBuilder::LOG_LEVEL => Level::Debug
        ];
        $builder = new OutLoggerBuilder($normal_config);
        $logger = $builder->buildLogger();

        assertInstanceOf(Logger::class, $logger,
            "Expected a logger");

        $handler = $logger->getHandlers()[0];
        assertInstanceOf(StreamHandler::class, $handler,
            "Expected a StreamHandler as first Handler");

        assertEmpty($builder->getWarnings(),
            "Normal build process. No warnings expected");
    }

    public function testBuildLoggerWithWarnings() {
        $missing_config = [
            AbstractLoggerBuilder::CHANNEL => "out",
        ];
        $builder = new OutLoggerBuilder($missing_config);
        $builder->setQuiet(true);
        $logger = $builder->buildLogger();

        assertInstanceOf(Logger::class, $logger,
            "Expected a logger");
        assertInstanceOf(Logger::class, $logger,
            "Things might have gone wrong, but we want a logger anyway");
        assertNotEmpty($builder->getWarnings(),
            "There should be warnings because the builder had no configuration");
    }

    public function testBuildLoggerWithFormatter() {
        $config = [
            AbstractLoggerBuilder::CHANNEL => "out",
            AbstractLoggerBuilder::LOG_LEVEL => Level::Debug,
            AbstractLoggerBuilder::LINE_FORMAT => "%level_name% | %datetime% > %message% | %context% %extra%\n",
            AbstractLoggerBuilder::DATE_FORMAT => "H:i:s:u",
        ];
        $builder = new OutLoggerBuilder($config);
        $logger = $builder->buildLogger();

        assertInstanceOf(Logger::class, $logger,
            "Expected a logger");
        assertEmpty($builder->getWarnings(),
            "Normal build process. No warnings expected");

        //$logger->info("This should be formatted");
    }


}
