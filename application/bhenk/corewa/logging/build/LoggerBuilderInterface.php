<?php

namespace bhenk\corewa\logging\build;

use Monolog\Logger;

interface LoggerBuilderInterface {

    function buildLogger(): Logger;

    function getWarnings() : array;

}