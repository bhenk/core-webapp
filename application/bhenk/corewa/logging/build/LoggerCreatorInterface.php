<?php

namespace bhenk\corewa\logging\build;

use Monolog\Logger;

interface LoggerCreatorInterface {

    function create(...$paras): Logger;

}