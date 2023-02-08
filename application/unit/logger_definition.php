<?php

/**
 * Provides logger configurations.
 *
 */

use Monolog\Level;

return [
    "stdout" => [
        "definition" => [
            "channel" => "out",
            "handlers" => [
                "handler01" => [
                    "class_name" => "bhenk\corewa\logging\handle\ConsoleHandler",
                    "paras" => [
                        "level" => Level::Debug,
                        "bubble" => false,
                        "white_line" => true,
                        "stack_match" => "/application\/(bhenk|unit)/i",
                    ],
                ],
            ],
        ],
    ],
    "stderr" => [
        "definition" => [
            "channel" => "err",
            "handlers" => [
                "handler01" => [
                    "class_name" => "Monolog\Handler\StreamHandler",
                    "paras" => [
                        "stream" => "php://stderr",
                        "level" => Level::Error,
                        "bubble" => true,
                        "filePermission" => null,
                        "useLocking" => false,
                    ],
                    "formatter" => [
                        "class_name" => "Monolog\Formatter\LineFormatter",
                        "paras" => [
                            "format" => "%level_name% | %datetime% > %message% | %context% %extra%\n",
                            "dateFormat" => "H:i:s:u",
                            "allowInlineLineBreaks" => true,
                            "ignoreEmptyContextAndExtra" => false,
                            "includeStacktraces" => true
                        ],
                    ],
                ],
            ],
            "processors" => [
                "processor01" => [
                    "class_name" => "Monolog\Processor\IntrospectionProcessor",
                    "paras" => [
                        "level" => Level::Debug,
                        "skipClassesPartials" => [],
                        "skipStackFramesCount" => 1,
                    ],
                ],
            ],
        ],
    ],
    "default" => [
        "definition" => [
            "channel" => "log",
            "handlers" => [
                "handler01" => [
                    "class_name" => "Monolog\Handler\RotatingFileHandler",
                    "paras" => [
                        "filename" => "logs/unit/app.log",
                        "maxFiles" => 2,
                        "level" => Level::Debug,
                        "bubble" => true,
                        "filePermission" => null,
                        "useLocking" => false
                    ],
                    "formatter" => [
                        "class_name" => "Monolog\Formatter\LineFormatter",
                        "paras" => [
                            "format" => "%level_name% | %datetime% > %message% | %context% %extra%\n",
                            "dateFormat" => "H:i:s:u",
                            "allowInlineLineBreaks" => true,
                            "ignoreEmptyContextAndExtra" => false,
                            "includeStacktraces" => true
                        ],
                    ],
                ],
                "handler02" => [
                    "class_name" => "Monolog\Handler\RotatingFileHandler",
                    "paras" => [
                        "filename" => "logs/unit/err.log",
                        "maxFiles" => 2,
                        "level" => Level::Error,
                        "bubble" => true,
                        "filePermission" => null,
                        "useLocking" => false
                    ],
                ],
            ],
        ],
    ],
    "req" => [
        "creator" => [
            "class_name" => "bhenk\corewa\logging\build\RequestLoggerCreator",
            "paras" => [
                "level" => Level::Info,
                "filename" => "logs/unit/req.log",
                "max_files" => 10,
                "filename_format" => "{filename}-{date}",
                "filename_date_format" => "Y-m",
                "format" => "%datetime% %extra%\n"
            ]
        ],
    ],
    "console_logger" => [
        "definition" => [
            "channel" => "clt",
            "handlers" => [
                "handler01" => [
                    "class_name" => "bhenk\corewa\logging\handle\ConsoleHandler",
                    "paras" => [
                        "level" => Level::Debug,
                        "bubble" => false,
                        "white_line" => true,
                        "stack_match" => "/application\/(bhenk|unit)/i",
                    ],
                ],
            ],
        ],
    ],
];