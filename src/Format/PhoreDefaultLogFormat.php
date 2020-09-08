<?php


namespace Phore\Log\Format;


use Phore\Log\PhoreLogger;
use Phore\Log\PhoreStopWatch;

class PhoreDefaultLogFormat implements PhoreLogFormat
{

    public function format(int $severity, string $file, int $lineNo, ...$params): string
    {
        $logLine = "[" . PhoreLogger::LOG_LEVEL_MAP[$severity] . "]";
        $logLine .= "[+" . str_pad(number_format(PhoreStopWatch::GetScriptRunTime(), 3, ".", ""), 7, " ", STR_PAD_LEFT) . "]";
        $logLine .= "[:" . str_pad($lineNo, 3, " ", STR_PAD_LEFT) . "]";
        $logLine .= " " . implode(" ", $params);
        return $logLine;
    }
}
