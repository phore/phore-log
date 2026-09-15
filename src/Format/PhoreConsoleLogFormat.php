<?php

namespace Phore\Log\Format;

use Phore\Log\LogLevelEnum;
use Phore\Log\LogRecord;
use Phore\Log\LogTypeEnum;

final class PhoreConsoleLogFormat implements PhoreLogFormat
{
    public function __construct(
        private bool $colors = true,
        private int $placeholderMaxLines = 5,
        private int $placeholderMaxChars = 240
    ) {
    }

    public function format(LogRecord $record): string
    {
        [$symbol, $color] = $this->appearance($record);
        $scope = '';
        if ($record->scope !== '') {
            $parts = explode('.', $record->scope);
            $scope = '[' . end($parts) . '] ';
        }
        $indent = str_repeat('  ', $record->depth);
        $message = $record->interpolatedMessage($this->placeholderMaxLines, $this->placeholderMaxChars);
        $context = $this->formatContext($record->context, $record->usedContextKeys());
        $line = $indent . $scope . $symbol . ' ' . $message . $context;
        return $this->colors ? "\033[{$color}m{$line}\033[0m" : $line;
    }

    private function appearance(LogRecord $record): array
    {
        return match ($record->type) {
            LogTypeEnum::SUCCESS => ['✓', '32'],
            LogTypeEnum::FAILURE => ['✗', '31'],
            LogTypeEnum::STEP => ['→', '36'],
            LogTypeEnum::SKIP => ['↷', '90'],
            LogTypeEnum::RESULT => ['=', '32'],
            LogTypeEnum::DETAIL => ['·', '90'],
            default => match ($record->level) {
                LogLevelEnum::EMERGENCY, LogLevelEnum::ALERT, LogLevelEnum::CRITICAL, LogLevelEnum::ERROR => ['✗', '31'],
                LogLevelEnum::WARNING => ['!', '33'],
                LogLevelEnum::DEBUG => ['·', '90'],
                default => ['ℹ', '37'],
            },
        };
    }

    private function formatContext(array $context, array $usedKeys): string
    {
        $parts = [];
        foreach ($context as $key => $value) {
            if (in_array((string)$key, $usedKeys, true)) {
                continue;
            }
            $formatted = $this->stringValue($value);
            if (str_contains($formatted, "\n")) {
                continue;
            }
            $parts[] = $key . '=' . $formatted;
        }
        return $parts === [] ? '' : '  ' . implode(' ', $parts);
    }

    private function stringValue(mixed $value): string
    {
        if ($value === null) return 'null';
        if (is_bool($value)) return $value ? 'true' : 'false';
        if (is_scalar($value) || $value instanceof \Stringable) return (string)$value;
        return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: get_debug_type($value);
    }
}
