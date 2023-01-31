<?php

namespace bhenk\corewa\logging;

class Err extends Log {

    private static Type $type = Type::stderr;

    public static function getType(): Type {
        return self::$type;
    }

    public static function setType(Type $type): Type {
        $previous = self::$type;
        self::$type = $type;
        return $previous;
    }

}