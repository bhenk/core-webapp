<?php /** @noinspection ALL */

namespace bhenk\corewa\logging;

use Attribute;
use Monolog\Level;

/**
 * Attribute to influence logging behavior.
 *
 * {@link Attribute::TARGET_CLASS} | {@link Attribute::TARGET_METHOD}
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class LogAttribute {

    /**
     * Constructs a {@link LogAttribute}.
     *
     * @param bool $on set attribute on or off. Default true.
     * @param Level $level set the level of log output. Default {@link Level::Debug}.
     */
    function __construct(bool  $on = true,
                         Level $level = Level::Debug
    ) {}

}