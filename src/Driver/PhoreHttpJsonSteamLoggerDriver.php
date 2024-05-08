<?php

namespace Phore\Log\Driver;

use Phore\Log\Format\PhoreDefaultLogFormat;
use Phore\Log\Format\PhoreLogFormat;
use Phore\Log\LogLevelEnum;

class PhoreHttpJsonSteamLoggerDriver implements PhoreLoggerDriver
{
    private $lastFile = null;


    /**
     * @var PhoreDefaultLogFormat
     */
    private $logFormat;

    private $minSeverity = 7;

    public function __construct()
    {
        $this->logFormat = new PhoreDefaultLogFormat();
    }

    public function log (LogLevelEnum $logLevel, string $file, int $lineNo, $message, $context = [])
    {
        if ($logLevel > $this->minSeverity)
            return;

        $logLine = $this->logFormat->format($logLevel, $file, $lineNo, $message, $context);
        $line = json_encode([
                "type" => "log",
                "level" => $logLevel,
                "file" => $file,
                "lineNo" => $lineNo,
                "message" => $logLine,
                "context" => $context
        ]);
        echo $line . "\r\n";
        //file_put_contents("php://output", $line . "\r\n");
        flush();
    }

    public function setMinSeverity(LogLevelEnum $logLevel)
    {
        $this->minSeverity = phore_loglevel_to_int($logLevel);
    }

    public function setFormatter(PhoreLogFormat $logFormat)
    {
        $this->logFormat = $logFormat;
    }
}
