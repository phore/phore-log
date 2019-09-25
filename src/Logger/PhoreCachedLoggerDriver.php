<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 07.08.18
 * Time: 08:30
 */

namespace Phore\Log\Logger;


use Phore\Log\PhoreStopWatch;

class PhoreCachedLoggerDriver implements PhoreLoggerDriver
{

    private $lastFile = null;

    private $logs = [];
    
    public function __construct()
    {
    }


    public function log (int $severity, string $file, int $lineNo, ...$params)
    {
        $severityMap = [
            0 => "DEBUG",
            1 => "DEBUG",
            2 => "INFO ",
            3 => "INFO ",
            4 => "WARN ",
            5 => "WARN ",
            6 => "WARN ",
            7 => "WARN ",
            8 => " ERR ",
            9 => "EMGY "
        ];

        if ($this->lastFile !== $file) {
            $this->lastFile = $file;
        }


        $logLine = "[{$severityMap[$severity]}]";
        $logLine .= "[" . str_pad(number_format(PhoreStopWatch::GetScriptRunTime(), 3, ".", ""), 7, " ", STR_PAD_LEFT);
        $logLine .= " +" . number_format(PhoreStopWatch::GetElapsedTime(), 3, ".", "") .  "s]";

        $logLine .= "[:" . str_pad($lineNo, 3, " ", STR_PAD_LEFT) . "]";
        $logLine .= " " . implode(" ", $params);
        $this->logs[] = $logLine;
    }

    /**
     * @return string[]
     */
    public function getLogs() : array 
    {
        return $this->logs;
    }
    
    
    public function getLogsAsString($prefix="# ") : string 
    {
        $out = "";
        foreach ($this->logs as $log) {
            $out .= "\n" . $prefix . $log;
        }
        return $out;
    }
    
}
