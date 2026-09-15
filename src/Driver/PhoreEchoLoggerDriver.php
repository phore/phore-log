<?php

namespace Phore\Log\Driver;

use Phore\Log\Format\PhoreDefaultLogFormat;
use Phore\Log\Format\PhoreLogFormat;
use Phore\Log\LogLevelEnum;
use Phore\Log\LogRecord;

class PhoreEchoLoggerDriver implements PhoreLoggerDriver
{
    private PhoreLogFormat $logFormat;
    private LogLevelEnum $minLevel = LogLevelEnum::DEBUG;

    public function __construct(private string $logTo = 'php://stderr')
    {
        $this->logFormat = new PhoreDefaultLogFormat();
    }

    public function log(LogRecord $record): void
    {
        if ($record->level->severity() > $this->minLevel->severity()) return;
        file_put_contents($this->logTo, $this->logFormat->format($record) . PHP_EOL, FILE_APPEND);
    }

    public function setSeverity(LogLevelEnum|string|int $severity): void
    {
        $this->setMinSeverity(LogLevelEnum::coerce($severity));
    }

    public function setMinSeverity(LogLevelEnum $logLevel): void
    {
        $this->minLevel = $logLevel;
    }

    public function setFormatter(PhoreLogFormat $logFormat): void
    {
        $this->logFormat = $logFormat;
    }
}
