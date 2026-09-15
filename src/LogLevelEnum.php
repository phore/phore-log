<?php

namespace Phore\Log;

enum LogLevelEnum: string
{
    case SUCCESS = 'success';
    case EMERGENCY = 'emergency';
    case ALERT = 'alert';
    case CRITICAL = 'critical';
    case ERROR = 'error';
    case WARNING = 'warning';
    case NOTICE = 'notice';
    case INFO = 'info';
    case DEBUG = 'debug';

    public function severity(): int
    {
        return match ($this) {
            self::EMERGENCY => 0,
            self::ALERT => 1,
            self::CRITICAL => 2,
            self::ERROR => 3,
            self::WARNING => 4,
            self::NOTICE => 5,
            self::INFO, self::SUCCESS => 6,
            self::DEBUG => 7,
        };
    }

    public static function coerce(self|string|int $level): self
    {
        if ($level instanceof self) {
            return $level;
        }
        if (is_int($level) || ctype_digit((string)$level)) {
            return match ((int)$level) {
                0 => self::EMERGENCY,
                1 => self::ALERT,
                2 => self::CRITICAL,
                3 => self::ERROR,
                4 => self::WARNING,
                5 => self::NOTICE,
                6 => self::INFO,
                7 => self::DEBUG,
                default => throw new \InvalidArgumentException("Unknown log severity '$level'")
            };
        }
        return self::tryFrom(strtolower($level))
            ?? throw new \InvalidArgumentException("Unknown log level '$level'");
    }
}
