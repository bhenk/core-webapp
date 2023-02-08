<?php

namespace unit\corewa\store\sql;

use bhenk\corewa\logging\handle\ConsoleLoggerTrait;
use bhenk\corewa\store\sql\MysqlConnector;
use mysqli;
use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertInstanceOf;
use function PHPUnit\Framework\assertNotSame;
use function PHPUnit\Framework\assertSame;

class MysqlConnectorTest extends TestCase {
    use ConsoleLoggerTrait;

    public function testConstructor() {
        $mysql = MysqlConnector::get();
        assertInstanceOf(MysqlConnector::class, $mysql);

        $conn01 = $mysql->getConnector();
        assertInstanceOf(mysqli::class, $conn01);

        MysqlConnector::closeConnection();
        $conn02 = $mysql->getConnector();
        assertInstanceOf(mysqli::class, $conn02);
        assertNotSame($conn01, $conn02);
        assertSame($mysql, MysqlConnector::get());
    }

}
