<?php

namespace bhenk\corewa\util;

use bhenk\corewa\conf\Config;
use Exception;
use function file_exists;
use function is_null;
use function str_starts_with;

class Path {

    /**
     * @throws Exception
     */
    public static function makeAbsolute(string $path, ?string $application_root = null): string {
        if (is_null($application_root)) {
            $application_root = Config::get()->getApplicationRoot();
        }
        if ($path == "" or $path == "/")
            throw new Exception("Argument cannot be empty string: \$config_file : '" . $path . "'");
        if (!str_starts_with($path, DIRECTORY_SEPARATOR)) {
            $path = $application_root . DIRECTORY_SEPARATOR . $path;
        }
        if (!file_exists($path))
            throw new Exception("File does not exists: '" . $path . "'");
        return $path;
    }

}