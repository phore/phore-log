<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 07.08.18
 * Time: 08:30
 */

namespace Phore\Log\Driver;


use Phore\Log\Format\PhoreDefaultLogFormat;
use Phore\Log\Format\PhoreLogFormat;
use Phore\Log\LogLevelEnum;
use Phore\Log\PhoreLogger;
use Phore\Log\PhoreStopWatch;

class PhoreCachedLoggerDriver implements PhoreLoggerDriver
{

    private $lastFile = null;

    private $logs = [];

    private $logFormat;

    private $minSeverity = 7;

    public function __construct()
    {
        $this->logFormat = new PhoreDefaultLogFormat();
    }


    public function log (LogLevelEnum $logLevel, string $file, int $lineNo, $message, $context = [])
    {
        if (phore_loglevel_to_int($logLevel) > $this->minSeverity)
            return;

        if ($this->lastFile !== $file) {
            $this->lastFile = $file;
        }


        $logLine = $this->logFormat->format($logLevel, $file, $lineNo, $message, $context);
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

    public function setMinSeverity(LogLevelEnum $logLevel)
    {
        $this->minSeverity = phore_loglevel_to_int($logLevel);
    }

    public function setFormatter(PhoreLogFormat $logFormat)
    {
        $this->logFormat = $logFormat;
    }
}
