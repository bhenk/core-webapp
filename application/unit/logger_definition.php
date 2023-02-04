<?php

use Monolog\Level;

return [
    "stdout" => [
        // creator or definition
        "definition" => [
            "channel" => "out",
            "handlers" => [
                "handler01" => [
                    "class_name" => "Monolog\Handler\StreamHandler",
                    "paras" => [
                        "stream" => "php://stdout",
                        "level" => Level::Debug,
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
];