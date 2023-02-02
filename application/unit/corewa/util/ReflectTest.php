<?php

namespace unit\corewa\util;

use bhenk\corewa\util\Reflect;
use Exception;
use PHPUnit\Framework\TestCase;

class ReflectTest extends TestCase {

    public function testCreateObject() {
        $class_name = "Exception";
        $paras = [
            "message" => "the message",
            "code" => 0,
            "previous" => null
        ];
        $object = Reflect::createObject($class_name, $paras);
        self::assertInstanceOf(Exception::class, $object);
    }

}
