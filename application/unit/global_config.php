<?php

use Monolog\Level;

$application_root = realpath(dirname(__DIR__));

return [
    "bhenk\corewa\logging\build\OutLoggerBuilder" => [
        "channel" => "out",
        "log_level" => Level::Debug,
        "line_format" => "%level_name% | %datetime% > %message% | %context% %extra%\n",
        "date_format" => "H:i:s:u",
    ],
    "bhenk\corewa\logging\build\ErrLoggerBuilder" => [
        "channel" => "err",
        "log_level" => Level::Debug,
        "line_format" => "%level_name% | %datetime% > %message% | %context% %extra%\n",
        "date_format" => "H:i:s:u",
    ],
    "bhenk\corewa\logging\build\DefaultLoggerBuilder" => [
        "channel" => "default",
        "log_level" => Level::Debug,
        "log_file" => $application_root . "/logs/unit/default.log",
        "max_log_files" => 2,
        "err_level" => Level::Error,
        "err_file" => $application_root . "/logs/unit/error.log",
        "max_err_files" => 2,
        "line_format" => "%level_name% | %datetime% > %message% | %context% %extra%\n",
        "date_format" => "H:i:s:u",
    ]
];