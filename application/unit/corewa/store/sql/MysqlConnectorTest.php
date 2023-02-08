<?php

namespace unit\corewa\store\sql;

use bhenk\corewa\conf\Config;
use bhenk\corewa\logging\handle\ConsoleLoggerTrait;
use bhenk\corewa\store\sql\MysqlConnector;
use mysqli;
use mysqli_sql_exception;
use unit\corewa\conf\AbstractConfigTestCase;
use function PHPUnit\Framework\assertInstanceOf;
use function PHPUnit\Framework\assertNotSame;
use function PHPUnit\Framework\assertSame;


class MysqlConnectorTest extends AbstractConfigTestCase {
    use ConsoleLoggerTrait;

    protected static bool $console_logger_trait_on = false;

    // will only work when testWrongPassword is first... ???
    public function testWrongPassword() {
        $config = Config::get()->getConfigurationFor(MysqlConnector::class);
        $config["password"] = "wrong pass";
        Config::get()->setConfigurationFor(MysqlConnector::class, $config);

        self::assertEquals("wrong pass",
            Config::get()->getConfigurationFor(MysqlConnector::class)["password"]);
        // but when we call MysqlConnector::get() it is still the correct password
        // if we run this method after a connection was established... ???
        self::assertTrue(true);
        $this->expectException(mysqli_sql_exception::class);
        MysqlConnector::get();
    }

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

}
