<?php

namespace bhenk\corewa\logging\handle;

interface ColorSchemeInterface {

    const    NL = "";
    const    RESET = "";
    const    END = "\033[0m";
    const    DEBUG = "";
    const    INFO = "";
    const    NOTICE = "";
    const    WARNING = "";
    const    ERROR = "";
    const    CRITICAL = "";
    const    ALERT = "";
    const    EMERGENCY = "";
    const    C_DATE = "";
    const    C_CLASS = "";
    const    C_FILE = "";
    const    C_CONTEXT = "";
    const    C_EXTRA = "";
    const    T_EXCL = "";
    const    T_BY = "";
    const    T_MSG = "";
    const    T_STACK = "";
    const    T_CAUSE = "";
    const    TRAIT_HELLO = "";
    const    TRAIT_METHOD = "";
    const    TRAIT_GOODBYE = "";
    const    TEST = "I'm a test";


}