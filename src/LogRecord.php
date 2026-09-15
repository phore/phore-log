<?php

namespace Phore\Log;

final readonly class LogRecord
{
    public function __construct(
        public float $timestamp,
        public LogLevelEnum $level,
        public LogTypeEnum $type,
        public string $message,
        public array $context,
        public string $scope,
        public int $depth,
        public string $file,
        public int $line
    ) {
    }

    public function interpolatedMessage(): string
    {
        $replace = [];
        foreach ($this->context as $key => $value) {
            if (is_scalar($value) || $value === null || $value instanceof \Stringable) {
                $replace['{' . $key . '}'] = (string)$value;
                $replace[':' . $key] = (string)$value;
            }
        }
        return strtr($this->message, $replace);
    }
}
