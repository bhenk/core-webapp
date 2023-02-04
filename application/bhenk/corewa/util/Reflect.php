<?php

namespace bhenk\corewa\util;

use Exception;
use function array_values;
use function count;
use function is_null;

class Reflect {

    /**
     * @param string $class_name
     * @param array $paras
     * @return mixed
     * @throws Exception
     */
    public static function createObject(string $class_name, array $paras = []): mixed {
        $object = null;
        $v = array_values($paras);
        switch (count($v)) {
            case 0:
                $object = new $class_name;
                break;
            case 1:
                $object = new $class_name($v[0]);
                break;
            case 2:
                $object = new $class_name($v[0], $v[1]);
                break;
            case 3:
                $object = new $class_name($v[0], $v[1], $v[2]);
                break;
            case 4:
                $object = new $class_name($v[0], $v[1], $v[2], $v[3]);
                break;
            case 5:
                $object = new $class_name($v[0], $v[1], $v[2], $v[3], $v[4]);
                break;
            case 6:
                $object = new $class_name($v[0], $v[1], $v[2], $v[3], $v[4], $v[5]);
                break;
            case 7:
                $object = new $class_name($v[0], $v[1], $v[2], $v[3], $v[4], $v[5], $v[6]);
                break;
        }
        if (is_null($object)) {
            throw new Exception(
                "Could not create object: " . $class_name . ". Count parameters exceeds 6. Given " . count($paras));
        }
        return $object;
    }

}