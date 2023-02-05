<?php

/**
 * Console colors for dark gray background.
 *
 * @see \bhenk\corewa\logging\handle\ConsoleHandler::$color_scheme
 */

return [
    "debug" => "\033[38;5;100m",
    "info" => "\033[38;5;107m",
    "notice" => "\033[38;5;111m",
    "warning" => "\033[38;5;128m",
    "error" => "\033[7m\033[38;5;124m",
    "critical" => "\033[7m\033[38;5;203m",
    "alert" => "\033[7m\033[38;5;199m",
    "emergency" => "\033[7m\033[38;5;196m",
    //
    "nl" => "\n ",
    "reset" => "\033[0m\033[48;5;236m\033[38;5;252m",
    //
    "date" => "\033[38;5;245m",
    "class" => "\033[38;5;245m",
    "file" => "\033[38;5;249m",
    "context" => "\033[38;5;104m",
    "extra" => "\033[38;5;104m",
    "t_excl" => "\033[1m\033[48;5;15m\033[38;5;124m",
    "t_by" => "\033[38;5;114m",
    "t_msg" => "\033[38;5;114m",
    "t_stack" => "\033[38;5;114m",
    "t_cause" => "\033[38;5;114m",
];
