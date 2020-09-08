<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 07.08.18
 * Time: 08:30
 */

namespace Phore\Log\Logger;


use Phore\Log\Format\PhoreDefaultLogFormat;
use Phore\Log\Format\PhoreLogFormat;
use Phore\Log\PhoreLogger;
use Phore\Log\PhoreStopWatch;

class PhoreCachedLoggerDriver implements PhoreLoggerDriver
{

    private $lastFile = null;

    private $logs = [];

    private $logFormat;

    public function __construct()
    {
        $this->logFormat = new PhoreDefaultLogFormat();
    }


    public function log (int $severity, string $file, int $lineNo, ...$params)
    {

        if ($this->lastFile !== $file) {
            $this->lastFile = $file;
        }


        $logLine = $this->logFormat->format($severity, $file, $lineNo, ...$params);
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

    public function setSeverity(int $severity)
    {
        // TODO: Implement setSeverity() method.
    }

    public function setFormatter(PhoreLogFormat $logFormat)
    {
        $this->logFormat = $logFormat;
    }
}
