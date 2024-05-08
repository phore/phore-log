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


class PhoreEchoLoggerDriver implements PhoreLoggerDriver
{

    private $lastFile = null;

    private $channel;

    /**
     * @var PhoreDefaultLogFormat
     */
    private $logFormat;

    private $minSeverity = 7;

    public function __construct($logTo="php://stderr")
    {
        $this->logFormat = new PhoreDefaultLogFormat();
        $this->channel = $logTo;
    }


    public function log (LogLevelEnum $logLevel, string $file, int $lineNo, $message, $context = [])
    {
        if (phore_loglevel_to_int($logLevel) > $this->minSeverity)
            return;

        $logLine = $this->logFormat->format($logLevel, $file, $lineNo, $message, $context);
        file_put_contents($this->channel, $logLine ."\n", FILE_APPEND);
    }

    public function setSeverity(LogLevelEnum $severity)
    {
        $this->minSeverity = phore_loglevel_to_int($severity);
    }

    public function setFormatter(PhoreLogFormat $logFormat)
    {
        $this->logFormat = $logFormat;
    }
}
