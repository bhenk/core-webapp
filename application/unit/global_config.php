<?php

use Monolog\Level;

return [
    "app\site\logging\build\OutLoggerBuilder" => [
        "channel" => "out",
        "log_level" => Level::Debug,
        "line_format" => "%level_name% | %datetime% > %message% | %context% %extra%\n",
        "date_format" => "H:i:s:u",
    ],
    "app\site\logging\build\ErrLoggerBuilder" => [
        "channel" => "err",
        "log_level" => Level::Debug,
        "line_format" => "%level_name% | %datetime% > %message% | %context% %extra%\n",
        "date_format" => "H:i:s:u",
    ],
];