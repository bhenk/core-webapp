<?php

return [
    "bhenk\corewa\conf\Version" => [
        "application" => "core-webapp",
        "version" => "0.0.1",
        "date" => "2023-02-14"
    ],
    "bhenk\corewa\logging\build\LoggerBuilder" => [
        "logger_definition_file" => "unit/logger_definition.php",
    ],
    "bhenk\corewa\data\sql\MysqlConnector" => [
        "hostname" => "127.0.0.1",
        "username" => "user",
        "password" => "user",
        "database" => "test",
    ],
];
