<?php

namespace bhenk\corewa\logging\build;

use bhenk\corewa\conf\Config;
use DateTimeInterface;
use Exception;
use InvalidArgumentException;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Level;
use Monolog\Logger;
use Monolog\Processor\WebProcessor;
use Psr\Log\LoggerInterface;

/**
 * Class capable of creating a request logger.
 *
 * The request logger outputs request properties to a file. Request properties are for instance
 * ...
 * * request url,
 * * ip address of the client,
 * * http_method,
 * * server,
 * * referrer,
 * * client browser
 *
 */
class RequestLoggerCreator implements LoggerCreatorInterface {

    /**
     * Creates a request logger.
     *
     * Parameters {@link $paras} have the format
     * ```
     *[
     *   "level" => Level,                      // optional, default Level::Debug
     *   "filename" => "{string}",              // required
     *   "max_files" => {int},                  // optional, default 2
     *   "filename_format" => "{string}",       // optional, default "{filename}-{date}"
     *   "filename_date_format" => "{string}",  // optional, default "Y-m"
     *   "line_format" => "{string}}"           // optional, default "%datetime% %extra%\n"
     *]
     * ```
     * The required *filename* may be relative to the *application root*.
     *
     * @param array $paras
     * @return LoggerInterface
     * @throws Exception
     */
    function create(array $paras = []): LoggerInterface {
        if (!isset($paras["filename"])) {
            throw new InvalidArgumentException("Missing parameter 'filename'");
        }
        $filename = Config::get()->makeAbsolute($paras["filename"], false);
        $handler = new RotatingFileHandler(
            $filename,
            $paras["max_files"] ?? 2,
            $paras["level"] ?? Level::Debug);
        $handler->setFilenameFormat(
            $paras["filename_format"] ?? "{filename}-{date}",
            $paras["filename_date_format"] ?? "Y-m");
        $formatter = new LineFormatter($paras["line_format"] ?? "%datetime% %extra%\n");
        $formatter->setDateFormat($paras["date_format"] ?? DateTimeInterface::W3C);
        $handler->setFormatter($formatter);
        $logger = new Logger($paras["channel"] ?? "req");
        $logger->pushHandler($handler);
        $logger->pushProcessor(function ($record) {
            $browser = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
            $record['extra']['browser'] = $browser;
            return $record;
        });
        $logger->pushProcessor(new WebProcessor());
        return $logger;
    }
}