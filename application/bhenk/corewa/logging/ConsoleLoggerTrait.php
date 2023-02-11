<?php /** @noinspection ALL */

namespace bhenk\corewa\logging\handle;

use bhenk\corewa\logging\Log;
use bhenk\corewa\logging\LoggerFactory;
use Monolog\Level;
use ReflectionClass;
use function print_r;
use function str_pad;

trait ConsoleLoggerTrait {

    private static string $CONSOLE_LOGGER = "console_logger";
    private static string $c = ColorSchemeDark::class;
    private static ReflectionClass $reflectionClass;
    private static bool $class_on = true;
    private static Level $class_level = Level::Debug;
    private bool $method_on = true;
    private Level $method_level = Level::Debug;
    private string $previous_type;

    public static function setUpBeforeClass(): void {
        self::$reflectionClass = new ReflectionClass(static::class);
        $attr_class = self::$reflectionClass->getAttributes(LogAttribute::class);
        if (!empty($attr_class)) {
            $args = $attr_class[0]->getArguments();
            self::$class_on = $args[0] ?? true;
            self::$class_level = $args[1] ?? Level::Debug;
        }
        if (self::$class_on) {
            /** @var ConsoleHandler $handler */
            $handler = LoggerFactory::get()->getLogger(self::$CONSOLE_LOGGER)->getHandlers()[0];
            self::$c = $handler->getConsoleColorScheme();
            print_r(PHP_EOL
                . self::$c::RESET
                . self::$c::TRAIT_HELLO
                . "hello " . static::class
                . self::$c::RESET
                . PHP_EOL);
        }
        parent::setUpBeforeClass();
    }

    public static function tearDownAfterClass(): void {
        if (self::$class_on) {
            print_r(self::$c::RESET
                . self::$c::TRAIT_GOODBYE
                . "goodbye " . static::class
                . self::$c::RESET
                . PHP_EOL);
        }
        parent::tearDownAfterClass();
    }

    public function setUp(): void {
        if (self::$class_on) {
            $reflection_method = self::$reflectionClass->getMethod(parent::getName());
            $attr_method = $reflection_method->getAttributes(LogAttribute::class);
            if (!empty($attr_method)) {
                $args = $attr_method[0]->getArguments();
                $this->method_on = $args[0] ?? true;
                $this->method_level = $args[1] ?? Level::Debug;
            }
            if ($this->method_on) {
                $this->previous_type = Log::setType("" . self::$CONSOLE_LOGGER . "");
                Log::setLevel($this->method_level);
                print_r(self::$c::RESET
                    . self::$c::TRAIT_METHOD
                    . str_pad(parent::getName(), 120, "-")
                    . self::$c::END
                    . PHP_EOL);
            }
        }
        parent::setUp();
    }

    public function tearDown(): void {
        if (self::$class_on and $this->method_on) {
            Log::setType($this->previous_type);
        }
        parent::tearDown();
    }
}