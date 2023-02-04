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



class ConsoleHandler extends AbstractHandler {

    private const C_RESET = " \033[0m";
    private const C_GREEN =  ""; //"\033[0;32m";
    private const C_BLUE = ""; //"\033[0;34m";
    private const C_FAT_GRAY = ""; //"\033[1;90m";
    private const C_FAT_RED = "\033[1;31m";
    private int $count = 0;

    /**
     *
     * @param int|string|Level $level
     * @param bool $bubble
     * @param string $date_format
     * @param string $stack_match
     * @param bool $white_line
     * @param string $exclamation
     */
    public function __construct(int|string|Level        $level = Level::Debug,
                                bool                    $bubble = true,
                                private readonly string $date_format = "H:i:s:u",
                                private readonly string $stack_match = "/(.*?)/i",
                                private readonly bool   $white_line = true,
                                private readonly string $exclamation = "chips!"
    ) {
        parent::__construct($level, $bubble);
    }

    /**
     * @inheritDoc
     */
    public function handle(LogRecord $record): bool {
        if (!$this->isHandling($record)) return $this->getBubble();

        $this->count += 1;
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
            . " " . self::C_FAT_GRAY . "[" . $class . $type . $function . "() $line]" . self::C_RESET
            . "> " . $message
            . "\n";
        $click = "file://" . $arr_file["file"] . ":" . $line . "\n";

        if ($this->white_line) print_r("\n");
        print_r($this->count . " ");
        print_r($row);
        print_r($click);

        foreach ($record->context as $key => $val) {
            $indent = "  ";
            if ($val instanceof Throwable) {
                self::printThrowable($val, $indent);
            } else {
                print_r(self::C_GREEN . $indent . "context: " . $key . " => " . self::C_RESET . $val . "\n");
            }
        }

        foreach ($record->extra as $key => $val) {
            $indent = "  ";
            if ($val instanceof Throwable) {
                self::printThrowable($val, $indent);
            } else {
                print_r(self::C_BLUE . $indent . "extra:   " . $key . " => " . self::C_RESET . $val . "\n");
            }
        }

        return $this->getBubble();
    }

    private function printThrowable(Throwable $t, string $indent): void {
        print_r(self::C_FAT_RED . $indent
            . $this->exclamation
            . " " . get_class($t)
            . " [code: " . $t->getCode() . "]"
            . self::C_RESET . "\n");
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