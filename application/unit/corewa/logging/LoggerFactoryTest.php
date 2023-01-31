<?php

namespace unit\corewa\logging;

use bhenk\corewa\logging\LoggerFactory;
use bhenk\corewa\logging\Type;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use function get_class;
use function PHPUnit\Framework\assertEmpty;
use function PHPUnit\Framework\assertInstanceOf;

class LoggerFactoryTest extends TestCase {

    public function testGetLogger() {
        // !! uses unit/global_config.php
        $logger = LoggerFactory::get()->getLogger(Type::stdout);
        assertInstanceOf(Logger::class, $logger,
            "Expected a logger");
        assertEmpty(LoggerFactory::get()->getWarnings(),
            "Logger stdout did not build");

        $logger = LoggerFactory::get()->getLogger(Type::stderr);
        assertInstanceOf(Logger::class, $logger,
            "Expected a logger");
        assertEmpty(LoggerFactory::get()->getWarnings(),
            "Logger stderr did not build");

        $logger = LoggerFactory::get()->getLogger(Type::default);
        assertInstanceOf(Logger::class, $logger,
            "Expected a logger");
        assertEmpty(LoggerFactory::get()->getWarnings(),
            "Logger default did not build");

        $logger->debug("test succeeded " . get_class($this));
        $logger->info("test succeeded " . get_class($this));
        $logger->notice("test succeeded " . get_class($this));
        $logger->warning("test succeeded " . get_class($this));
        $logger->error("test succeeded " . get_class($this));
        $logger->critical("test succeeded " . get_class($this));
        $logger->alert("test succeeded " . get_class($this));
        $logger->emergency("test succeeded " . get_class($this));
    }
}
