<?php

namespace unit\corewa\logging;

use bhenk\corewa\logging\Out;
use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertTrue;

class LogTest extends TestCase {

    public function testStatic() {
        $this->doSome();
    }

    public function doSome() {
        $this->tatic();
    }

    public function tatic() {
        if (UNIT_IS_LOUD) {
            Out::debug("debug test succeeded ");
//            Out::info("test succeeded " . get_class($this));
//            Out::notice("test succeeded " . get_class($this));
//            Out::warning("test succeeded " . get_class($this));
//            Out::error("test succeeded " . get_class($this));
//            Out::critical("test succeeded " . get_class($this));
//            Out::alert("test succeeded " . get_class($this));
//            Out::emergency("test succeeded " . get_class($this));
//
//            Err::debug("test succeeded " . get_class($this));
//            Err::info("test succeeded " . get_class($this));
//            Err::notice("test succeeded " . get_class($this));
//            Err::warning("test succeeded " . get_class($this));
//            Err::error("test succeeded " . get_class($this));
//            Err::critical("test succeeded " . get_class($this));
//            Err::alert("test succeeded " . get_class($this));
//            Err::emergency("test succeeded " . get_class($this), [new Exception("testing")]);

//            Log::debug("test new " . get_class($this));
//            Log::info("test succeeded " . get_class($this));
//            Log::notice("test succeeded " . get_class($this));
//            Log::warning("test succeeded " . get_class($this));
//            Log::error("test succeeded " . get_class($this));
//            Log::critical("test succeeded " . get_class($this));
//            Log::alert("test succeeded " . get_class($this));
//            Log::emergency("test succeeded " . get_class($this));
//            Log::emergency("met ", [new Exception("test")]);
        }
        assertTrue(true);
    }

}
