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


    public function log (int $severity, string $file, int $lineNo, ...$params)
    {
        if ($severity > $this->minSeverity)
            return;

        $logLine = $this->logFormat->format($severity, $file, $lineNo, ...$params);
        file_put_contents($this->channel, $logLine ."\n", FILE_APPEND);
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
