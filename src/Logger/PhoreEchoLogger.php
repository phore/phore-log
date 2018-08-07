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
            1 => "INFO",
            2 => "WARN",
            3 => "ERR"
        ];

        if ($this->lastFile !== $file) {
            echo "> $file\n";
            $this->lastFile = $file;
        }


        $logLine = "[{$severityMap[$severity]}]";
        $logLine .= "[+" . number_format(PhoreStopWatch::GetScriptRunTime(), 2) . "]";
        $logLine .= "[:$lineNo]";
        $logLine .= " " . implode(" ", $params);
        echo $logLine ."\n";
    }

}
