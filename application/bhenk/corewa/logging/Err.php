<?php

namespace bhenk\corewa\logging;

/**
 * StdErr log agent.
 *
 * The type of {@link LoggerInterface} used by this log agent defaults to "stderr".
 *
 * Documentation of logging calls copied from {@link LoggerInterface}.
 *
 * @see LoggerFactory::getLogger()
 */
class Err extends Log {

    private static string $type = "stderr";

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