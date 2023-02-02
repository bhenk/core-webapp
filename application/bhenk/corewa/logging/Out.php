<?php

namespace bhenk\corewa\logging;

class Out extends Log {

    private static string $type = "stdout";

    public static function getType(): string {
        return self::$type;
    }

    public static function setType(string $type): string {
        $previous = self::$type;
        self::$type = $type;
        return $previous;
    }

}