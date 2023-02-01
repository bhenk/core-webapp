<?php

namespace unit\corewa\logging;

use bhenk\corewa\logging\build\LoggerBuilderInterface;
use bhenk\corewa\logging\LoggerFactory;
use bhenk\corewa\logging\Type;
use Exception;
use Monolog\Logger;
use PHPUnit\Framework\Error\Error;
use PHPUnit\Framework\TestCase;
use function get_class;
use function PHPUnit\Framework\assertContains;
use function PHPUnit\Framework\assertEmpty;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertInstanceOf;
use function PHPUnit\Framework\assertNotEmpty;
use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertTrue;
use PHPUnit\Framework\MockObject\IncompatibleReturnValueException;
use function var_dump;

class LoggerFactoryTest extends TestCase {

    public function setUp(): void {
        parent::setUp();
        LoggerFactory::get()->reset();
        //LoggerFactory::get()->setQuiet(true);
    }

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
        if (UNIT_IS_LOUD) {
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


    public function testGetCustomLogger() {
        $expected_logger = new Logger("testing");
        $stub = $this->getMockBuilder(LoggerBuilderInterface::class)->getMock();
        $stub->method("buildLogger")
            ->willReturn($expected_logger);
        $stub->method("getWarnings")
            ->willReturn([]);
        $logger = LoggerFactory::get()->getCustomLogger("testing", $stub);

        assertEquals($expected_logger, $logger);
        assertEmpty(LoggerFactory::get()->getWarnings());
    }

    public function testGetCustomLoggerWithWarnings() {
        $expected_logger = new Logger("testing");
        $expected_warning = ["builder message: Could not build custom logger, created default logger"];
        $stub = $this->getMockBuilder(LoggerBuilderInterface::class)->getMock();
        $stub->method("buildLogger")
            ->willReturn($expected_logger);
        $stub->method("getWarnings")
            ->willReturn($expected_warning);
        $logger = LoggerFactory::get()->getCustomLogger("testing01", $stub);

        assertEquals($expected_logger, $logger);
        assertSame(LoggerFactory::get()->getWarnings(), $expected_warning);
    }

    public function testGetCustomLoggerWithNull() {
        $expected_logger = null;
        $stub = $this->getMockBuilder(LoggerBuilderInterface::class)->getMock();

        $this->expectException(IncompatibleReturnValueException::class);
        $stub->method("buildLogger")
            ->willReturn($expected_logger);
    }

    public function testGetCustomLoggerWithError() {
        //LoggerFactory::get()->setQuiet(false);
        $exception_message = "exception messaged";
        $expected_warning = ["builder message: Could not build custom logger, something very bad happened"];
        $stub = $this->getMockBuilder(LoggerBuilderInterface::class)->getMock();
        $stub->method("buildLogger")
            ->willThrowException(new Exception($exception_message));
        $stub->method("getWarnings")
            ->willReturn($expected_warning);
        $logger = LoggerFactory::get()->getCustomLogger("testing02", $stub);

        assertEquals("out", $logger->getName());
        $warnings = LoggerFactory::get()->getWarnings();
        assertEquals(3, count($warnings));
        assertContains($exception_message, $warnings);
        assertContains($expected_warning[0], $warnings);
    }




}
