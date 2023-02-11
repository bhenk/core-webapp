<?php

namespace bhenk\corewa\logging;

/**
 * StdOut log agent.
 *
 * The type of {@link LoggerInterface} used by this log agent defaults to "stdout".
 *
 * Documentation of logging calls copied from {@link LoggerInterface}.
 *
 * @see LoggerFactory::getLogger()
 */
class Out extends Log {

    private static string $type = "stdout";

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