<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 07.08.18
 * Time: 08:30
 */

namespace Phore\Log\Logger;


use Phore\Log\PhoreLogger;
use Phore\Log\PhoreStopWatch;

class PhoreEchoLoggerDriver implements PhoreLoggerDriver
{

    private $lastFile = null;

    private $channel;
    
    public function __construct($logTo="php://stderr")
    {
        $this->channel = $logTo;
    }


    public function log (int $severity, string $file, int $lineNo, ...$params)
    {
        if ($this->lastFile !== $file) {
            file_put_contents($this->channel,"> $file\n", FILE_APPEND);
            $this->lastFile = $file;
        }


        $logLine = "[" . PhoreLogger::LOG_LEVEL_MAP[$severity] . "]";
        $logLine .= "[+" . str_pad(number_format(PhoreStopWatch::GetScriptRunTime(), 3, ".", ""), 7, " ", STR_PAD_LEFT) . "]";
        $logLine .= "[:" . str_pad($lineNo, 3, " ", STR_PAD_LEFT) . "]";
        $logLine .= " " . implode(" ", $params);
        file_put_contents($this->channel, $logLine ."\n", FILE_APPEND);
    }

}
