<?php

namespace bhenk\corewa\logging\handle;

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

enum Colors: string {
    case debug = "\033[1;90m ";
    case info = "\033[1;36m ";
    case notice = "\033[1;34m ";
    case warning = "\033[1;35m ";
    case error = "\033[1;31m\033[103m ";
    case critical = "\033[1;97m\033[41m ";
    case alert = "\033[1;97m\033[105m ";
    case emergency = "\033[1;97m\033[101m ";

    public static function fromName(string $name): string {
        $result = "";
        foreach (self::cases() as $level) {
            if ($name === $level->name) {
                $result = $level->value;
            }
        }
        return $result;
    }
}

class ConsoleHandler extends AbstractHandler {

    const C_RESET = " \033[0m";

    /**
     *
     * @param int|string|Level $level
     * @param bool $bubble
     * @param string $date_format
     * @param string $stack_match
     * @param string $exclamation
     */
    public function __construct(int|string|Level $level = Level::Debug,
                                bool             $bubble = true,
                                private string   $date_format = "H:i:s:u",
                                private string   $stack_match = "/(.*?)/i",
                                private string   $exclamation = "chips!") {
        parent::__construct($level, $bubble);
    }

    /**
     * @inheritDoc
     */
    public function handle(LogRecord $record): bool {
        if (!$this->isHandling($record)) return $this->getBubble();

        $level = $record->level->toPsrLogLevel();
        $color = Colors::fromName($level);
        $level = str_pad(strtoupper($level), 9);
        $date = $record->datetime->format($this->date_format);
        $message = $record->message;
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5);
        $arr_file = $backtrace[3];
        $a_caller = $backtrace[4];
        $class = $a_caller["class"];
        $class = substr($class, strrpos($class, "\\") + 1);
        $type = $a_caller["type"];
        $function = $a_caller["function"];
        $line = $arr_file["line"];

        $row = $color . $level . self::C_RESET
            . " " . $date
            . " \033[1;90m[" . $class . $type . $function . "() $line]" . self::C_RESET
            . "> " . $message
            . "\n";
        $click = "file://" . $arr_file["file"] . ":" . $line . "\n";

        print_r($row);
        print_r($click);

        foreach ($record->context as $key => $val) {
            $indent = "  ";
            if ($val instanceof Throwable) {
                self::printThrowable($val, $indent);
            } else {
                print_r($indent . "\033[0;32mcontext: " . $key . " => \033[0m " . $val . "\n");
            }
        }

        foreach ($record->extra as $key => $val) {
            $indent = "  ";
            if ($val instanceof Throwable) {
                self::printThrowable($val, $indent);
            } else {
                print_r($indent . "\033[0;34mextra  : " . $key . " => \033[0m " . $val . "\n");
            }
        }

        return $this->getBubble();
    }

    private function printThrowable(Throwable $t, string $indent): void {
        print_r("\033[01;31m" . $indent
            . $this->exclamation
            . " " . get_class($t)
            . " [code: " . $t->getCode() . "]"
            . "\033[0m\n");
        print_r($indent . " Thrown by: file://" . $t->getFile() . ":" . $t->getLine() . "\n");
        print_r($indent . " Message: " . $t->getMessage() . "\n");
        print_r($indent . " Stacktrace:\n");
        foreach ($t->getTrace() as $trace) {
            if (preg_match($this->stack_match, $trace["file"])) {
                print_r($indent . "   >   file://"
                    . $trace["file"] . ":"
                    . $trace["line"]
                    . " -> " . $trace["function"]
                    . "\n");
            }
        }
        if (!is_null($t->getPrevious())) {
            print_r($indent . " Caused by:\n");
            $indent = $indent . $indent . $indent;
            $this->printThrowable($t->getPrevious(), $indent);
        }
    }


}