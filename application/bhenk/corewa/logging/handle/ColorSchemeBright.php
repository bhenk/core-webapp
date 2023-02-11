<?php

namespace bhenk\corewa\logging\handle;

class ColorSchemeBright implements ColorSchemeInterface {

    const    NL = "\n ";
    const    RESET = "\033[0m\033[48;5;231m\033[38;5;235m";
    const    DEBUG = "\033[38;5;100m";
    const    INFO = "\033[38;5;107m";
    const    NOTICE = "\033[38;5;111m";
    const    WARNING = "\033[38;5;128m";
    const    ERROR = "\033[7m\033[38;5;124m";
    const    CRITICAL = "\033[7m\033[38;5;203m";
    const    ALERT = "\033[7m\033[38;5;199m";
    const    EMERGENCY = "\033[7m\033[38;5;196m";
    const    C_DATE = "\033[38;5;245m";
    const    C_CLASS = "\033[38;5;245m";
    const    C_FILE = "\033[38;5;249m";
    const    CONTEXT = "\033[38;5;104m";
    const    EXTRA = "\033[38;5;104m";
    const    T_EXCL = "\033[1m\033[48;5;15m\033[38;5;124m";
    const    T_BY = "\033[38;5;114m";
    const    T_MSG = "\033[38;5;114m";
    const    T_STACK = "\033[38;5;114m";
    const    T_CAUSE = "\033[38;5;114m";
    const    TRAIT_HELLO = "";
    const    TRAIT_METHOD = "\033[38;5;40m";
    const    TRAIT_GOODBYE = "\033[38;5;110m";
    const    TEST = "I am a " . self::class;

}