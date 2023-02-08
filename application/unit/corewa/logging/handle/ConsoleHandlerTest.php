<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

namespace unit\corewa\logging\handle;

use bhenk\corewa\logging\handle\ConsoleHandler;
use bhenk\corewa\logging\LoggerFactory;
use bhenk\corewa\logging\Out;
use InvalidArgumentException;
use LengthException;
use Monolog\Handler\AbstractHandler;
use Monolog\Level;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Stringable;
use Throwable;
use function PHPUnit\Framework\assertInstanceOf;

class ConsoleHandlerTest extends TestCase {

    private bool $loud = false;

    public function testHandler() {
        $ch = new ConsoleHandler();
        if ($this->loud) $this->practicalTest();
        self::assertInstanceOf(AbstractHandler::class, $ch);
    }

    private function practicalTest(): void {
        $ch = new ConsoleHandler(
            Level::Debug,
            true,
            true,
            "/application\/(bhenk|unit)/i",
            null,
            null,
            null
        );
        $logger = new Logger("unit");
        $logger->pushHandler($ch);
        Out::notice("Changing logger for Out");

        $previous_logger = LoggerFactory::get()->setLogger("stdout", $logger);
        Out::debug("Changed logger for Out");
        $this->stack01();
        Out::debug("Testing colors");
        Out::info("Testing colors");
        Out::notice("Testing colors");
        Out::warning("Testing colors");
        Out::error("Testing colors");
        Out::critical("Testing colors");
        Out::alert("Testing colors");
        Out::emergency("Testing colors");
        LoggerFactory::get()->setLogger("stdout", $previous_logger);
    }

    private function stack01(): void {
        $foo = new class implements Stringable {
            public function __toString(): string {
                return "Object __toString() bla bla";
                // return "foo\033[0;42m \033[3;97mb\nar \033[0m";
            }
        };
        assertInstanceOf(Stringable::class, $foo);
        Out::info($foo);
        try {
            $this->stack02();
        } catch (Throwable $e) {
            Out::error("Caught an exception", ["first", "second", $e, "last"]);
        }
    }

    private function stack02(): void {
        Out::notice("Go on with stack");
        $this->catchHavoc();
    }

    private function catchHavoc() {
        Out::notice("Think there's trouble");
        try {
            $this->goOn();
        } catch (Throwable $e) {
            Out::warning("Caught an Exception, going to throw it");
            throw new LengthException("Too long!", 55, $e);
        }
    }

    private function goOn() {
        Out::notice("Carry on....");
        $this->causeHavoc();
    }

    private function causeHavoc(): void {
        Out::emergency("Not good in debating...");
        throw new InvalidArgumentException("The exception message", 42);
    }

}
