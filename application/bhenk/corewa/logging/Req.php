<?php

namespace bhenk\corewa\logging;

/**
 * A log agent for recording request parameters.
 *
 * The type of {@link LoggerInterface} used by this log agent defaults to "req".
 *
 * Documentation of logging calls copied from {@link LoggerInterface}.
 *
 * @see LoggerFactory::getLogger()
 */
class Req extends Log {

    private static string $type = "req";

    /**
     * @inheritDoc
     *
     * @return string
     */
    public static function getType(): string {
        return self::$type;
    }

    /**
     * @inheritDoc
     *
     * @param string $type
     * @return string
     */
    public static function setType(string $type): string {
        $previous = self::$type;
        self::$type = $type;
        return $previous;
    }

}