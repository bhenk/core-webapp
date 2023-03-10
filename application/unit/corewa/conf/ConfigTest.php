<?php

namespace unit\corewa\conf;

use bhenk\corewa\conf\Config;
use Exception;
use PHPUnit\Framework\TestCase;
use function get_class;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertIsArray;
use function PHPUnit\Framework\assertNotSame;
use function PHPUnit\Framework\assertNull;
use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertTrue;

class ConfigTest extends TestCase {

    private string $application_root;
    private string $config_file;

    private string $config_file_01 = __DIR__ . DIRECTORY_SEPARATOR . "test_config01.php";

    public function setUp(): void {
        $this->application_root = Config::get()->getApplicationRoot();
        $this->config_file = Config::get()->getConfigFile();
    }

    public function tearDown(): void {
        Config::load($this->application_root, $this->config_file);
    }

    public function testGet() {
        Config::reset();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Instance not loaded");
        Config::get();
    }

    public function testLoad() {
        Config::load($this->application_root, $this->config_file_01);
        $instance1 = Config::get();
        $config = Config::get()->getConfig();

        assertIsArray($config);
        assertEquals(3, count($config));
        assertTrue($config["foo"]);

        $instance2 = Config::load($this->application_root, $this->config_file_01);
        assertNotSame($instance1, $instance2);
        assertSame($instance2, Config::get());
    }

    public function testLoadWithError01() {
        $this->expectException(Exception::class);
        Config::load($this->application_root, "/x.x");
    }

    public function testLoadWithError02() {
        $this->expectException(Exception::class);
        Config::load($this->application_root, "x.x");
    }

    public function testLoadWithError03() {
        $this->expectException(Exception::class);
        Config::load($this->application_root, "");
    }

    public function testLoadWithError04() {
        $this->expectException(Exception::class);
        Config::load($this->application_root, "/");
    }

    public function testSetConfig() {
        $new_config = ["bar"];
        Config::load($this->application_root, $this->config_file_01);
        Config::get()->setConfig($new_config);
        assertEquals(["bar"], Config::get()->getConfig());
    }

    public function testGetConfigurationFor() {
        Config::load($this->application_root, $this->config_file_01);
        $myConfig = Config::get()->getConfigurationFor(get_class($this));
        assertEquals("a unit test", $myConfig["description"]);
    }

    public function testGetConfigurationForNonexistent() {
        Config::load($this->application_root, $this->config_file_01);
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("not set or null");
        Config::get()->getConfigurationFor("me");
    }

    public function testSetConfigurationFor() {
        Config::load($this->application_root, $this->config_file_01);
        $new_config = ["bar"];
        $previous = Config::get()->setConfigurationFor("cat", $new_config);

        assertNull($previous);
        assertEquals($new_config, Config::get()->getConfigurationFor("cat"));
    }


}
