<?php

namespace Phore\Log\Driver;

use Phore\Log\Format\PhoreLogFormat;
use Phore\Log\LogLevelEnum;
use Phore\Log\LogRecord;

class PhoreHttpJsonSteamLoggerDriver implements PhoreLoggerDriver
{
    private LogLevelEnum $minLevel = LogLevelEnum::DEBUG;

    public function log(LogRecord $record): void
    {
        if ($record->level->severity() > $this->minLevel->severity()) return;
        $line = json_encode([
            'type' => $record->type->value,
            'level' => $record->level->value,
            'scope' => $record->scope,
            'file' => $record->file,
            'lineNo' => $record->line,
            'message' => $record->message,
            'context' => $record->context,
            'timestamp' => $record->timestamp
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";
        echo dechex(strlen($line)) . "\r\n" . $line . "\r\n";
        flush();
    }

    public function setMinSeverity(LogLevelEnum $logLevel): void
    {
        $this->minLevel = $logLevel;
    }

    public function setFormatter(PhoreLogFormat $logFormat): void
    {
    }
}
