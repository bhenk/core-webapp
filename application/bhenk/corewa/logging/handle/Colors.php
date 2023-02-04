<?php

namespace bhenk\corewa\logging\handle;

enum Colors: string {
    case debug = "\033[1;90m ";
    case info = "\033[1;36m ";
    case notice = "\033[1;34m ";
    case warning = "\033[1;35m ";
    case error = "\033[1;31m\033[103m ";
    case critical = "\033[1;97m\033[41m ";
    case alert = "\033[1;97m\033[105m ";
    case emergency = "\033[1;97m\033[1;101m ";

    public static function fromName(string $name): string {
        $result = "";
        foreach (self::cases() as $level) {
            if ($name === $level->name) {
                $result = $level->value;
                break;
            }
        }
        return $result;
    }
}