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
        private int $placeholderMaxChars = 240,
        private int $lineWidth = 120
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
        $prefix = $indent . $scope . $symbol . ' ';
        $message = $record->interpolatedMessage($this->placeholderMaxLines, $this->placeholderMaxChars);
        $context = $this->formatContext($record->displayContext(), $record->usedContextKeys());
        $line = $this->formatMultiline($prefix, $message . $context);
        return $this->colors ? "\033[{$color}m{$line}\033[0m" : $line;
    }

    private function formatMultiline(string $prefix, string $content): string
    {
        $prefixWidth = $this->displayWidth($prefix);
        $continuation = str_repeat(' ', $prefixWidth);
        $contentWidth = max(20, $this->lineWidth - $prefixWidth);
        $logicalLines = preg_split('/\R/u', $content) ?: [$content];
        $output = [];
        $first = true;

        foreach ($logicalLines as $logicalLine) {
            $wrappedLines = $this->wrapLine($logicalLine, $contentWidth);
            foreach ($wrappedLines as $wrappedLine) {
                $output[] = ($first ? $prefix : $continuation) . $wrappedLine;
                $first = false;
            }
        }

        return implode(PHP_EOL, $output);
    }

    /** @return string[] */
    private function wrapLine(string $line, int $width): array
    {
        if ($line === '' || $this->displayWidth($line) <= $width) {
            return [$line];
        }

        $wrapped = wordwrap($line, $width, "\n", true);
        return explode("\n", $wrapped);
    }

    private function displayWidth(string $value): int
    {
        return function_exists('mb_strwidth') ? mb_strwidth($value) : strlen($value);
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
            $formatted = $this->displayValue($value);
            if (str_contains($formatted, "\n")) {
                continue;
            }
            $parts[] = $key . '=' . $formatted;
        }
        return $parts === [] ? '' : '  ' . implode(' ', $parts);
    }

    private function displayValue(mixed $value): string
    {
        if ($value === null) return 'null';
        if (is_bool($value)) return $value ? 'true' : 'false';
        if (is_string($value)) return "'" . str_replace(['\\', "'"], ['\\\\', "\\'"], $value) . "'";
        if (is_int($value) || is_float($value)) return (string)$value;
        if ($value instanceof \Stringable) return "'" . str_replace(['\\', "'"], ['\\\\', "\\'"], (string)$value) . "'";
        return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: get_debug_type($value);
    }
}
