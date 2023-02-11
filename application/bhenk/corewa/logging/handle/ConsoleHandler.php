<?php

namespace bhenk\corewa\logging\handle;

use Monolog\Handler\AbstractHandler;
use Monolog\Level;
use Monolog\LogRecord;
use Throwable;
use function constant;
use function debug_backtrace;
use function get_class;
use function is_null;
use function preg_match;
use function print_r;
use function str_pad;
use function strrpos;
use function strtoupper;
use function substr;


/**
 * Handler class for displaying log output on console.
 */
class ConsoleHandler extends AbstractHandler {

    private array $console_colors;
    private int $count = 0;

    /**
     * Displays log output on console.
     *
     * This handler is especially equipped to be used during development. See {@link ConsoleLoggerTrait} on how to
     * switch logging to console for a particular TestCase.
     *
     * The {@link $stack_match} parameter expects a regular expression. It can be used to suppress the amount of
     * stacktrace elements of {@link Throwable}s. Par example, the regex
     * <code>"/application\/(bhenk|unit)/i"</code> will only print traces of files that have either
     * <code>/application/bhenk</code> or <code>/application/unit</code> in their filename.
     * Defaults to <code>"/(.*?)/i"</code> - all files.
     *
     * The {@link $date_format} defaults to a short <code>"H:i:s:u"</code>.
     *
     *
     * See also {@link AbstractHandler}.
     *
     * @param int|string|Level $level accepted minimum logging level
     * @param bool $bubble controls the bubbling process of the handler stack
     * @param bool $white_line print empty line above each log statement (default true)
     * @param string|null $stack_match reg-ex to match filenames in stack traces
     * @param string|null $date_format date format for printed log statements
     * @param string|null $exclamation thrown in when a throwable is reported
     * @param string|null $color_scheme color scheme for this handler
     */
    public function __construct(int|string|Level      $level = Level::Debug,
                                bool                  $bubble = false,
                                private readonly bool $white_line = true,
                                private ?string       $stack_match = null,
                                private ?string       $date_format = null,
                                private ?string       $exclamation = null,
                                private ?string       $color_scheme = null
    ) {
        parent::__construct($level, $bubble);
        if (is_null($this->date_format)) $this->date_format = "H:i:s:u";
        if (is_null($this->stack_match)) $this->stack_match = "/(.*?)/i";
        if (is_null($this->exclamation)) $this->exclamation = "chips!";
        if (is_null($this->color_scheme)) $this->color_scheme = ColorSchemeDark::class;
    }

    public function getConsoleColorScheme(): string {
        return $this->color_scheme;
    }

    /**
     * @inheritDoc
     */
    public function handle(LogRecord $record): bool {
        if (!$this->isHandling($record)) return $this->getBubble();
        $c = $this->color_scheme;

        $this->count += 1;
        $level = strtoupper($record->level->toPsrLogLevel());
        $level_color = constant("$c::" . $level);
        $level = str_pad($level, 9);
        $date = $record->datetime->format($this->date_format);
        $message = $record->message;
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5);
        $arr_file = $backtrace[3];
        $a_caller = $backtrace[4] ?? null;
        $class = $a_caller["class"] ?? null;
        if (!is_null($class)) $class = substr($class, strrpos($class, "\\") + 1);
        $type = $a_caller["type"] ?? null;
        $function = $a_caller["function"] ?? null;
        $braces = (is_null($function)) ? null : "()";
        $line = $arr_file["line"];

        $row = "$level_color $level " . $c::RESET
            . $c::C_DATE . " $date " . $c::RESET
            . $c::C_CLASS . " [$class$type$function$braces $line] " . $c::RESET
            . "> " . $message . $c::RESET
            . $c::NL;
        $click = $c::C_FILE . "file://" . $arr_file["file"] . ":$line" . $c::RESET . $c::NL;

        print_r($c::RESET);
        if ($this->white_line) print_r($c::NL);
        print_r("$this->count ");
        print_r($row);
        print_r($click);

        $this->printArray($record->context, "context", $c);
        $this->printArray($record->extra, "extra", $c);

        print_r($c::END);
        return $this->getBubble();
    }

    private function printArray(array $arr, string $word, string $c): void {
        foreach ($arr as $key => $val) {
            $indent = "  ";
            if ($val instanceof Throwable) {
                self::printThrowable($val, $indent, $c);
            } else {
                print_r($indent . constant("$c::" . strtoupper($word))
                    . " $word: $key => " . $c::RESET
                    . PHP_EOL
                    . $val . $c::RESET . $c::NL);
            }
        }
    }

    private function printThrowable(Throwable $t, string $indent, string $c): void {
        print_r($c::T_EXCL . $indent
            . " " . $this->exclamation
            . " " . get_class($t)
            . " [code: " . $t->getCode() . "] "
            . $c::RESET . $c::NL);
        print_r($indent . $c::T_BY . " Thrown by:  " . $c::RESET
            . " file://" . $t->getFile() . ":" . $t->getLine()
            . $c::RESET . $c::NL);
        print_r($indent . $c::T_MSG . " Message:    " . $c::RESET
            . " " . $t->getMessage() . $c::NL);
        print_r($indent . $c::T_STACK . " Stacktrace: " . $c::RESET
            . $c::NL);

        $previous = null;
        foreach (array_reverse($t->getTrace()) as $trace) {

            if (preg_match($this->stack_match, $trace["file"])) {
                print_r($indent . "  >  file://"
                    . $trace["file"] . ":"
                    . $trace["line"]
                    . " => " . $previous . "()"
                    . $c::NL);
            }
            $previous = $trace["function"];
        }
        if (!is_null($t->getPrevious())) {
            print_r($indent . $c::T_CAUSE . " Caused by:  " . $c::RESET
                . $c::NL);
            $indent = $indent . $indent . $indent;
            $this->printThrowable($t->getPrevious(), $indent, $c);
        }
    }


}