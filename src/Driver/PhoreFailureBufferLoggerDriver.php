<?php

namespace Phore\Log\Driver;

use Phore\Log\Format\PhoreLogFormat;
use Phore\Log\LogLevelEnum;
use Phore\Log\LogRecord;

final class PhoreFailureBufferLoggerDriver implements PhoreLoggerDriver
{
    /** @var LogRecord[] */
    private array $buffer = [];
    private LogLevelEnum $minLevel;

    public function __construct(
        private PhoreLoggerDriver $target,
        private LogLevelEnum $triggerLevel = LogLevelEnum::ERROR,
        private int $capacity = 200,
        LogLevelEnum $minLevel = LogLevelEnum::DEBUG
    ) {
        if ($this->capacity < 1) {
            throw new \InvalidArgumentException('Failure buffer capacity must be at least 1');
        }
        $this->minLevel = $minLevel;
    }

    public function log(LogRecord $record): void
    {
        if ($record->level->severity() <= $this->triggerLevel->severity()) {
            $this->flush();
            $this->target->log($record);
            return;
        }

        if ($record->level->severity() > $this->minLevel->severity()) {
            return;
        }

        $this->buffer[] = $record;
        if (count($this->buffer) > $this->capacity) {
            array_shift($this->buffer);
        }
    }

    public function flush(): void
    {
        foreach ($this->buffer as $record) {
            $this->target->log($record);
        }
        $this->buffer = [];
    }

    public function clear(): void
    {
        $this->buffer = [];
    }

    public function getBufferedCount(): int
    {
        return count($this->buffer);
    }

    public function setMinSeverity(LogLevelEnum $logLevel): void
    {
        $this->minLevel = $logLevel;
    }

    public function setFormatter(PhoreLogFormat $logFormat): void
    {
        $this->target->setFormatter($logFormat);
    }
}
