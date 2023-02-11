<?php

namespace bhenk\corewa\logging;

use bhenk\corewa\logging\handle\ColorSchemeInterface;
use bhenk\corewa\logging\handle\ConsoleHandler;
use Monolog\Level;
use ReflectionClass;
use ReflectionException;
use RuntimeException;
use function method_exists;
use function print_r;
use function str_pad;

/**
 * Trait capable of redirecting log output to console.
 *
 * Caveat: only log statements logged via {@link Log} will be redirected. Test cases that want to inspect
 * log output of classes under test use this trait. It is recommended to use this trait together with
 * {@link LogAttribute}.
 *
 * Example usage:
 *
 * <code>
 *      class SomeTest extends TestCase {
 *          use ConsoleLoggerTrait;
 *      ...
 * </code>
 * All log statements via {@link Log} will be redirected during runtime of the TestCase, permanently.
 * At least until you remove the <code>use</code> statement. When more flexibility is required also use
 * {@link LogAttribute}.
 *
 *  <code>
 *      #[LogAttribute()]
 *      class SomeTest extends TestCase {
 *          use ConsoleLoggerTrait;
 *      ...
 * </code>
 * All log statements via {@link Log} will be redirected. To stop redirecting log statements for the entire
 * TestCase just type <code>false</code> as the first argument for {@link LogAttribute}. To change the
 * {@link Level} of all log statements that pass to console do something like
 * <code>#[LogAttribute(true, Level::Error)]</code>. When more fine grained control is needed use
 * {@link LogAttribute} also on individual test methods.
 *
 * <code>
 *          #[LogAttribute(false)]
 *          public function testSomeFeature() : void {
 *               ...
 *          }
 * </code>
 * Suppress all logging via console of code touched by SomeFeature. When revisiting the test method just
 * change the LogAttribute parameter to true. Optionally change the level of log statements seen via console
 * as well.
 *
 * The on/off setting of {@link LogAttribute} on class level has precedence over that on method level. A class
 * with <code>#[LogAttribute(false)]</code> will never output via console.
 *
 * The setting of the level parameter of {@link LogAttribute} on individual methods has precedence over that set
 * on class level.
 *
 * This trait calls on {@link Log} to set the type of logger temporarily to <code>"console_logger"</code>. Skies
 * look bright if the logger of this type has the handler {@link ConsoleHandler}. If so, this trait will use
 * the {@link ColorSchemeInterface} set on this handler. Otherwise, a RuntimeException will be thrown with
 * the message that you messed up the code.
 */
trait ConsoleLoggerTrait {

    private static string $CONSOLE_LOGGER = "console_logger";
    private static ColorSchemeInterface $cs;
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
            $logger = LoggerFactory::get()->getLogger(self::$CONSOLE_LOGGER);
            if (method_exists($logger, "getHandlers")) {
                /** @var ConsoleHandler $handler */
                $handler = $logger->getHandlers()[0];
                self::$cs = $handler->getColorScheme();
            } else {
                // unreachable code
                throw new RuntimeException("Code mess: expected " . ConsoleHandler::class);
            }
            print_r(PHP_EOL
                . self::$cs::RESET
                . self::$cs::TRAIT_HELLO
                . "hello " . static::class
                . self::$cs::RESET
                . PHP_EOL);
        }
        parent::setUpBeforeClass();
    }

    public static function tearDownAfterClass(): void {
        if (self::$class_on) {
            print_r(self::$cs::RESET
                . self::$cs::TRAIT_GOODBYE
                . "goodbye " . static::class
                . self::$cs::RESET
                . PHP_EOL);
        }
        parent::tearDownAfterClass();
    }

    /**
     * @throws ReflectionException
     */
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
                $this->previous_type = Log::setType(self::$CONSOLE_LOGGER);
                Log::setLevel($this->method_level);
                print_r(self::$cs::RESET
                    . self::$cs::TRAIT_METHOD
                    . str_pad(parent::getName(), 120, "-")
                    . self::$cs::END
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