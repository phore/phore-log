<?php

namespace Phore\Log\Driver;

use Phore\Log\Format\PhoreConsoleLogFormat;
use Phore\Log\Format\PhoreLogFormat;
use Phore\Log\LogLevelEnum;
use Phore\Log\LogRecord;

final class PhoreConsoleLoggerDriver implements PhoreLoggerDriver
{
    private mixed $stream;
    private PhoreLogFormat $formatter;
    private LogLevelEnum $minLevel = LogLevelEnum::DEBUG;

    public function __construct(string $target = 'php://stderr', ?bool $colors = null)
    {
        $this->stream = fopen($target, 'ab');
        if ($this->stream === false) {
            throw new \RuntimeException("Cannot open console target '$target'");
        }
        $colors ??= function_exists('stream_isatty') && stream_isatty($this->stream);
        $this->formatter = new PhoreConsoleLogFormat($colors);
    }

    public function log(LogRecord $record): void
    {
        if ($record->level->severity() > $this->minLevel->severity()) {
            return;
        }
        fwrite($this->stream, $this->formatter->format($record) . PHP_EOL);
    }

    public function setMinSeverity(LogLevelEnum $logLevel): void
    {
        $this->minLevel = $logLevel;
    }

    public function setFormatter(PhoreLogFormat $logFormat): void
    {
        $this->formatter = $logFormat;
    }

    public function __destruct()
    {
        if (is_resource($this->stream)) {
            fclose($this->stream);
        }
    }
}
