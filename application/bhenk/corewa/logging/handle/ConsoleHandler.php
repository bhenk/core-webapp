<?php

namespace bhenk\corewa\logging\handle;

use bhenk\corewa\logging\Log;
use bhenk\corewa\logging\LoggerFactory;
use Monolog\Handler\AbstractHandler;
use Monolog\Level;
use Monolog\LogRecord;
use Throwable;
use function debug_backtrace;
use function get_class;
use function is_null;
use function preg_match;
use function print_r;
use function str_pad;
use function strrpos;
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
     * The {@link $color_scheme} defaults to {@link ConsoleColors.php}, a dark theme.
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
        if (is_null($this->color_scheme)) $this->color_scheme = __DIR__ . DIRECTORY_SEPARATOR . "ConsoleColors.php";
        $this->console_colors = require $this->color_scheme;
    }

    /**
     * @inheritDoc
     */
    public function handle(LogRecord $record): bool {
        if (!$this->isHandling($record)) return $this->getBubble();

        $this->count += 1;
        $cc = $this->console_colors;
        $level = $record->level->toPsrLogLevel();
        $level_color = $cc[$level];
        $level = str_pad(strtoupper($level), 9);
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

        $row = "$level_color $level " . $cc["reset"]
            . $cc["date"] . " $date " . $cc["reset"]
            . $cc['class'] . " [$class$type$function$braces $line] " . $cc["reset"]
            . "> " . $message . $cc["reset"]
            . $cc["nl"];
        $click = $cc["file"] . "file://" . $arr_file["file"] . ":$line" . $cc["reset"] . $cc["nl"];

        print_r($cc["reset"]);
        if ($this->white_line) print_r($cc["nl"]);
        print_r("$this->count ");
        print_r($row);
        print_r($click);

        $this->printArray($record->context, "context", $cc);
        $this->printArray($record->extra, "extra", $cc);

        print_r("\033[0m");
        return $this->getBubble();
    }

    private function printArray(array $arr, string $word, array $cc): void {
        foreach ($arr as $key => $val) {
            $indent = "  ";
            if ($val instanceof Throwable) {
                self::printThrowable($val, $indent, $cc);
            } else {
                print_r($indent . $cc[$word] . " $word: $key => " . $cc["reset"]
                    . $val . $cc["reset"] . $cc["nl"]);
            }
        }
    }

    private function printThrowable(Throwable $t, string $indent, array $cc): void {
        print_r($cc["t_excl"] . $indent
            . " " . $this->exclamation
            . " " . get_class($t)
            . " [code: " . $t->getCode() . "] "
            . $cc["reset"] . $cc["nl"]);
        print_r($indent . $cc["t_by"] . " Thrown by:  " . $cc["reset"]
            . " file://" . $t->getFile() . ":" . $t->getLine()
            . $cc["reset"] . $cc["nl"]);
        print_r($indent . $cc["t_msg"] . " Message:    " . $cc["reset"]
            . " " . $t->getMessage() . $cc["nl"]);
        print_r($indent . $cc["t_stack"] . " Stacktrace: " . $cc["reset"]
            . $cc["nl"]);

        $previous = null;
        foreach (array_reverse($t->getTrace()) as $trace) {

            if (preg_match($this->stack_match, $trace["file"])) {
                print_r($indent . "  >  file://"
                    . $trace["file"] . ":"
                    . $trace["line"]
                    . " => " . $previous . "()"
                    . $cc["nl"]);
            }
            $previous = $trace["function"];
        }
        if (!is_null($t->getPrevious())) {
            print_r($indent . $cc["t_cause"] . " Caused by:  " . $cc["reset"]
                . $cc["nl"]);
            $indent = $indent . $indent . $indent;
            $this->printThrowable($t->getPrevious(), $indent, $cc);
        }
    }


}