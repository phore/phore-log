<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 07.08.18
 * Time: 08:30
 */

namespace Phore\Log\Logger;


use Phore\Log\PhoreStopWatch;

class PhoreEchoLogger implements PhoreLogger
{

    private $lastFile = null;

    public function log (int $severity, string $file, int $lineNo, ...$params)
    {
        $severityMap = [
            0 => "DEBUG",
            1 => "INFO ",
            2 => "WARN ",
            3 => " ERR "
        ];

        if ($this->lastFile !== $file) {
            echo "> $file\n";
            $this->lastFile = $file;
        }


        $logLine = "[{$severityMap[$severity]}]";
        $logLine .= "[+" . str_pad(number_format(PhoreStopWatch::GetScriptRunTime(), 3, ".", ""), 7, " ", STR_PAD_LEFT) . "]";
        $logLine .= "[:" . str_pad($lineNo, 3, " ", STR_PAD_LEFT) . "]";
        $logLine .= " " . implode(" ", $params);
        echo $logLine ."\n";
    }

}
