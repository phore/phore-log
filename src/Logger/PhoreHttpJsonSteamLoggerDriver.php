<?php

namespace Phore\Log\Logger;

use Phore\Log\Format\PhoreDefaultLogFormat;
use Phore\Log\Format\PhoreLogFormat;

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

    public function log (int $severity, string $file, int $lineNo, ...$params)
    {
        if ($severity > $this->minSeverity)
            return;

        $logLine = $this->logFormat->format($severity, $file, $lineNo, ...$params);
        echo json_encode([
            "type" => "log",
            "severity" => $severity,
                "file" => $file,
            "lineNo" => $lineNo,
            "message" => $logLine,
                "context" => $params
        ]) . PHP_EOL;
        flush();
    }

    public function setSeverity(int $severity)
    {
        $this->minSeverity = $severity;
    }

    public function setFormatter(PhoreLogFormat $logFormat)
    {
        $this->logFormat = $logFormat;
    }
}
