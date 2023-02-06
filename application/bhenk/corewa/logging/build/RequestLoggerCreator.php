<?php

namespace bhenk\corewa\logging\build;

use bhenk\corewa\conf\Config;
use DateTimeInterface;
use InvalidArgumentException;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Level;
use Monolog\Logger;
use Monolog\Processor\WebProcessor;
use Psr\Log\LoggerInterface;

class RequestLoggerCreator implements LoggerCreatorInterface {


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
        $formatter = new LineFormatter($paras["format"] ?? "%datetime% %extra%\n");
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