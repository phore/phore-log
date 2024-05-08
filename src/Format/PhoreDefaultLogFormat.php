<?php


namespace Phore\Log\Format;


use Phore\Log\LogLevelEnum;
use Phore\Log\PhoreLogger;
use Phore\Log\PhoreStopWatch;

class PhoreDefaultLogFormat implements PhoreLogFormat
{

    public function __construct(private bool $level = true, private bool $time = true, private bool $lineNo = true)
    {

    }


    public function format(LogLevelEnum $level, string $file, int $lineNo, string $message, array $context = []) : string
    {
        $logLine = "";
        if ($this->level)
            $logLine = "[" . $level->name . "]";
        if ($this->time)
            $logLine .= "[+" . str_pad(number_format(PhoreStopWatch::GetScriptRunTime(), 3, ".", ""), 7, " ", STR_PAD_LEFT) . "]";

        if ($this->lineNo)
            $logLine .= "[:" . str_pad($lineNo, 3, " ", STR_PAD_LEFT) . "]";
        $logLine .= " " . $message;
        return $logLine;
    }
}
