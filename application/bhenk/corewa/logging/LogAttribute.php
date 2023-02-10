<?php /** @noinspection ALL */

namespace bhenk\corewa\logging\handle;

use Attribute;
use Monolog\Level;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class LogAttribute {

    function __construct(bool  $on = true,
                         Level $level = Level::Debug
    ) {}

}