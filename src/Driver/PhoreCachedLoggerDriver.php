<?php

namespace Phore\Log\Driver;

use Phore\Log\Format\PhoreDefaultLogFormat;
use Phore\Log\Format\PhoreLogFormat;
use Phore\Log\LogLevelEnum;
use Phore\Log\LogRecord;

class PhoreCachedLoggerDriver implements PhoreLoggerDriver
{
    private array $logs = [];
    private PhoreLogFormat $logFormat;
    private LogLevelEnum $minLevel = LogLevelEnum::DEBUG;

    public function __construct()
    {
        $this->logFormat = new PhoreDefaultLogFormat();
    }

    public function log(LogRecord $record): void
    {
        if ($record->level->severity() > $this->minLevel->severity()) return;
        $this->logs[] = $this->logFormat->format($record);
    }

    public function getLogs(): array
    {
        return $this->logs;
    }

    public function getLogsAsString(string $prefix = '# '): string
    {
        return implode('', array_map(fn(string $log) => "\n" . $prefix . $log, $this->logs));
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
