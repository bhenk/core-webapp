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
                            "allowInlineLineBreaks" => false,
                            "ignoreEmptyContextAndExtra" => false,
                            "includeStacktraces" => false
                        ],
                    ],
                ],
            ],
        ],
    ],
];