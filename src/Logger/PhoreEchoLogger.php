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

    private $channel;
    
    public function __construct($logTo="php://stderr")
    {
        $this->channel = $logTo;
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
            file_put_contents($this->channel,"> $file\n", FILE_APPEND);
            $this->lastFile = $file;
        }


        $logLine = "[{$severityMap[$severity]}]";
        $logLine .= "[+" . str_pad(number_format(PhoreStopWatch::GetScriptRunTime(), 3, ".", ""), 7, " ", STR_PAD_LEFT) . "]";
        $logLine .= "[:" . str_pad($lineNo, 3, " ", STR_PAD_LEFT) . "]";
        $logLine .= " " . implode(" ", $params);
        file_put_contents($this->channel, $logLine ."\n", FILE_APPEND);
    }

}
