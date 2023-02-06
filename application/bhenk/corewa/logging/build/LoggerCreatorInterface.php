<?php

namespace bhenk\corewa\logging\build;

use Psr\Log\LoggerInterface;

interface LoggerCreatorInterface {

    function create(array $paras = []): LoggerInterface;

}