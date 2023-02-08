<?php

namespace unit\corewa\conf;

use bhenk\corewa\conf\Config;
use PHPUnit\Framework\TestCase;

abstract class AbstractConfigTestCase extends TestCase {

    private array $config;

    public function setUp(): void {
        $this->config = Config::get()->getConfig();
    }

    public function tearDown(): void {
        Config::get()->setConfig($this->config);
    }

}