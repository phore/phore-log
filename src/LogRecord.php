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

    public function interpolatedMessage(?int $defaultMaxLines = null): string
    {
        $openBrace = "\x00PHORE_OPEN_BRACE\x00";
        $closeBrace = "\x00PHORE_CLOSE_BRACE\x00";
        $message = str_replace(['{{', '}}'], [$openBrace, $closeBrace], $this->message);

        $message = preg_replace_callback(
            '/\{([A-Za-z_][A-Za-z0-9_.-]*)(?::([^{}]+))?\}/',
            function (array $match) use ($defaultMaxLines): string {
                $key = $match[1];
                if (!array_key_exists($key, $this->context)) {
                    return $match[0];
                }

                $filters = isset($match[2]) ? array_values(array_filter(array_map('trim', explode('|', $match[2])))) : [];
                return $this->formatPlaceholderValue($this->context[$key], $filters, $defaultMaxLines);
            },
            $message
        ) ?? $message;

        return str_replace([$openBrace, $closeBrace], ['{', '}'], $message);
    }

    /** @return string[] */
    public function usedContextKeys(): array
    {
        $message = str_replace(['{{', '}}'], ['', ''], $this->message);
        preg_match_all('/\{([A-Za-z_][A-Za-z0-9_.-]*)(?::[^{}]+)?\}/', $message, $matches);
        return array_values(array_unique($matches[1] ?? []));
    }

    private function formatPlaceholderValue(mixed $value, array $filters, ?int $defaultMaxLines): string
    {
        $full = in_array('full', $filters, true);
        $hasLinesFilter = false;
        $formatted = $this->stringValue($value);

        foreach ($filters as $filter) {
            if ($filter === 'full') {
                continue;
            }
            if ($filter === 'json') {
                $formatted = json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: get_debug_type($value);
                continue;
            }
            if ($filter === 'ms') {
                if (!is_numeric($value)) {
                    throw new \InvalidArgumentException('The :ms placeholder format requires a numeric value');
                }
                $milliseconds = rtrim(rtrim(number_format((float)$value * 1000, 3, '.', ''), '0'), '.');
                $formatted = $milliseconds . ' ms';
                continue;
            }
            if (preg_match('/^trim=(\d+)$/', $filter, $match)) {
                $formatted = $this->trimCharacters($formatted, (int)$match[1]);
                continue;
            }
            if (preg_match('/^lines=(\d+)$/', $filter, $match)) {
                $hasLinesFilter = true;
                $formatted = $this->trimLines($formatted, (int)$match[1]);
                continue;
            }
            throw new \InvalidArgumentException("Unknown placeholder format '$filter'");
        }

        if (!$full && !$hasLinesFilter && $defaultMaxLines !== null) {
            $formatted = $this->trimLines($formatted, $defaultMaxLines);
        }

        return $formatted;
    }

    private function stringValue(mixed $value): string
    {
        if ($value === null) return 'null';
        if (is_bool($value)) return $value ? 'true' : 'false';
        if (is_scalar($value) || $value instanceof \Stringable) return (string)$value;
        return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: get_debug_type($value);
    }

    private function trimCharacters(string $value, int $limit): string
    {
        if ($limit < 1) {
            return '';
        }
        $length = function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);
        if ($length <= $limit) {
            return $value;
        }
        $trimmed = function_exists('mb_substr') ? mb_substr($value, 0, $limit) : substr($value, 0, $limit);
        return $trimmed . '… +' . ($length - $limit) . ' chars';
    }

    private function trimLines(string $value, int $limit): string
    {
        if ($limit < 1) {
            return '';
        }
        $lines = preg_split('/\R/u', $value) ?: [$value];
        if (count($lines) <= $limit) {
            return $value;
        }
        $hidden = count($lines) - $limit;
        return implode(PHP_EOL, array_slice($lines, 0, $limit)) . PHP_EOL . '… +' . $hidden . ' lines';
    }
}
