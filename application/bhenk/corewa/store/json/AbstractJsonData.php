<?php

namespace bhenk\corewa\store\json;

use bhenk\corewa\conf\Config;
use JsonSerializable;
use function dirname;
use function file_exists;
use function is_null;

abstract class AbstractJsonData implements JsonSerializable {

    /**
     * Get the name of the file for reading and storing this data.
     *
     * @return string filename
     */
    public abstract function getFile(): string;

    public function load(): object {
        $file = Config::get()->makeAbsolute($this->getFile(), false);
        $obj = (file_exists($file)) ? json_decode(file_get_contents($file)) : (object)[];
        // empty file: null is returned if the json cannot be decoded
        return (is_null($obj)) ? (object)[] : $obj;
    }

    public function persist(): ?int {
        $file = Config::get()->makeAbsolute($this->getFile(), false);
        $this->makeDirectories($file);
        return file_put_contents($file,
            json_encode($this->jsonSerialize(),
                JSON_PRETTY_PRINT + JSON_UNESCAPED_SLASHES),
            LOCK_EX);
    }

    private function makeDirectories($file) {
        $directories = dirname($file);
        if (!is_dir($directories)) {
            mkdir($directories, 0777, TRUE);
        }
    }
}