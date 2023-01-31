<?php

namespace bhenk\corewa\logging;

class Out extends Log {

    private static Type $type = Type::stdout;

    public static function getType(): Type {
        return self::$type;
    }

    public static function setType(Type $type): Type {
        $previous = self::$type;
        self::$type = $type;
        return $previous;
    }

}