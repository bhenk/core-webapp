<?php

namespace unit\corewa\store\sql;

use bhenk\corewa\logging\handle\ConsoleLoggerTrait;
use bhenk\corewa\logging\Log;
use bhenk\corewa\store\sql\MysqlConnector;
use mysqli;
use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertInstanceOf;
use function PHPUnit\Framework\assertNotSame;
use function PHPUnit\Framework\assertSame;


class MysqlConnectorTest extends TestCase {
    use ConsoleLoggerTrait;

    private static bool $console_logger_trait_on = false;

    public function testConnection() {
        $mysql = MysqlConnector::get();
        assertInstanceOf(MysqlConnector::class, $mysql);

        $mysqli01 = $mysql->getConnector();
        assertInstanceOf(mysqli::class, $mysqli01);

        MysqlConnector::closeConnection();
        $mysqli02 = $mysql->getConnector();
        assertInstanceOf(mysqli::class, $mysqli02);
        assertNotSame($mysqli01, $mysqli02);
        assertSame($mysql, MysqlConnector::get());
    }

    public function testNothing() {
        Log::notice("message from test nothing");
        self::assertTrue(true);
    }

}
