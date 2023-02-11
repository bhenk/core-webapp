<?php

namespace bhenk\corewa\logging\build;

use Psr\Log\LoggerInterface;

/**
 * Defines an interface for classes that are capable of creating loggers that implement {@link LoggerInterface}.
 *
 */
interface LoggerCreatorInterface {

    /**
     * Creates a logger that implement {@link LoggerInterface}.
     *
     * @param array $paras array of name-value pairs needed for building the logger.
     * @return LoggerInterface
     */
    function create(array $paras = []): LoggerInterface;

}