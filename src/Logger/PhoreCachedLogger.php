<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 07.08.18
 * Time: 08:30
 */

namespace Phore\Log\Logger;


use Phore\Log\PhoreStopWatch;

class PhoreCachedLogger implements PhoreLogger
{

    private $lastFile = null;

    private $logs = [];
    
    public function __construct()
    {
    }


    public function log (int $severity, string $file, int $lineNo, ...$params)
    {
        $severityMap = [
            9 => "DEBUG",
            5 => "INFO ",
            3 => "WARN ",
            2 => " ERR ",
            1 => "EMGY "
        ];

        if ($this->lastFile !== $file) {
            $this->lastFile = $file;
        }


        $logLine = "[{$severityMap[$severity]}]";
        $logLine .= "[+" . str_pad(number_format(PhoreStopWatch::GetScriptRunTime(), 3, ".", ""), 7, " ", STR_PAD_LEFT) . "]";
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
            $out .= $prefix . $log;
        }
        return $out;
    }
    
}
