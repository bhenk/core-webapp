<?php

namespace bhenk\corewa\logging;

class Req extends Log {

    private static string $type = "req";

    public static function getType(): string {
        return self::$type;
    }

    public static function setType(string $type): string {
        $previous = self::$type;
        self::$type = $type;
        return $previous;
    }

}