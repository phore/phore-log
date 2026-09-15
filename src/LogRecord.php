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

    public function interpolatedMessage(?int $defaultMaxLines = null, ?int $defaultMaxChars = null): string
    {
        $openBrace = "\x00PHORE_OPEN_BRACE\x00";
        $closeBrace = "\x00PHORE_CLOSE_BRACE\x00";
        $message = str_replace(['{{', '}}'], [$openBrace, $closeBrace], $this->message);

        $message = preg_replace_callback(
            '/\{([A-Za-z_][A-Za-z0-9_.-]*)(?::([^{}]+))?\}/',
            function (array $match) use ($defaultMaxLines, $defaultMaxChars): string {
                $key = $match[1];
                $entry = $this->resolveContextEntry($key);
                if ($entry === null) {
                    return $match[0];
                }

                $templateFilters = isset($match[2])
                    ? $this->parseFilters($match[2])
                    : [];
                $filters = $templateFilters !== [] ? $templateFilters : $entry['filters'];

                return $this->formatPlaceholderValue($entry['value'], $filters, $defaultMaxLines, $defaultMaxChars);
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

    /**
     * Returns context for human-readable suffix output without key-level format metadata.
     * The original structured context remains untouched in the record.
     */
    public function displayContext(): array
    {
        $result = [];
        foreach ($this->context as $key => $value) {
            if (!is_string($key)) {
                $result[$key] = $value;
                continue;
            }

            [$baseKey] = $this->parseContextKey($key);
            if (!array_key_exists($baseKey, $result) || $baseKey === $key) {
                $result[$baseKey] = $value;
            }
        }
        return $result;
    }

    private function resolveContextEntry(string $key): ?array
    {
        if (array_key_exists($key, $this->context)) {
            return ['value' => $this->context[$key], 'filters' => []];
        }

        foreach ($this->context as $contextKey => $value) {
            if (!is_string($contextKey)) {
                continue;
            }
            [$baseKey, $filters] = $this->parseContextKey($contextKey);
            if ($baseKey === $key) {
                return ['value' => $value, 'filters' => $filters];
            }
        }

        return null;
    }

    private function parseContextKey(string $key): array
    {
        if (preg_match('/^([A-Za-z_][A-Za-z0-9_.-]*):(.+)$/', $key, $match)) {
            return [$match[1], $this->parseFilters($match[2])];
        }
        if (preg_match('/^([A-Za-z_][A-Za-z0-9_.-]*)\|(.+)$/', $key, $match)) {
            return [$match[1], $this->parseFilters($match[2])];
        }
        return [$key, []];
    }

    private function parseFilters(string $filters): array
    {
        return array_values(array_filter(array_map('trim', explode('|', $filters))));
    }

    private function formatPlaceholderValue(mixed $value, array $filters, ?int $defaultMaxLines, ?int $defaultMaxChars): string
    {
        $full = false;
        $json = false;
        $serialize = false;
        $milliseconds = false;
        $toFile = false;
        $decimals = null;
        $trim = null;
        $lines = null;

        foreach ($filters as $filter) {
            if ($filter === 'full') {
                $full = true;
                continue;
            }
            if ($filter === 'json') {
                $json = true;
                continue;
            }
            if ($filter === 'serialize') {
                $serialize = true;
                continue;
            }
            if ($filter === 'ms') {
                $milliseconds = true;
                continue;
            }
            if ($filter === 'file') {
                $toFile = true;
                continue;
            }
            if (preg_match('/^(?:dec|decimal)=(\d+)$/', $filter, $match)) {
                $decimals = (int)$match[1];
                continue;
            }
            if (preg_match('/^trim=(\d+)$/', $filter, $match)) {
                $trim = (int)$match[1];
                continue;
            }
            if (preg_match('/^lines=(\d+)$/', $filter, $match)) {
                $lines = (int)$match[1];
                continue;
            }
            throw new \InvalidArgumentException("Unknown placeholder format '$filter'");
        }

        if ($milliseconds || $decimals !== null) {
            if (!is_numeric($value)) {
                throw new \InvalidArgumentException('Numeric placeholder formats require a numeric value');
            }
            $number = (float)$value;
            if ($milliseconds) {
                $number *= 1000;
            }
            if ($decimals !== null) {
                $formatted = number_format($number, $decimals, '.', '');
            } else {
                $formatted = rtrim(rtrim(number_format($number, 3, '.', ''), '0'), '.');
            }
            if ($milliseconds) {
                $formatted .= ' ms';
            }
        } elseif ($serialize) {
            $formatted = serialize($value);
        } elseif ($json) {
            $formatted = json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?: get_debug_type($value);
        } elseif ($toFile && is_object($value)) {
            $formatted = serialize($value);
        } elseif ($toFile && is_array($value)) {
            $formatted = json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?: '[]';
        } else {
            $formatted = $this->stringValue($value);
        }

        if ($toFile) {
            return LogPlaceholderFileStore::write($formatted);
        }

        if ($lines !== null) {
            $formatted = $this->trimLines($formatted, $lines);
        } elseif (!$full && $defaultMaxLines !== null) {
            $formatted = $this->trimLines($formatted, $defaultMaxLines);
        }

        if ($trim !== null) {
            $formatted = $this->trimCharacters($formatted, $trim);
        } elseif (!$full && $defaultMaxChars !== null) {
            $formatted = $this->trimCharacters($formatted, $defaultMaxChars);
        }

        return $formatted;
    }

    private function stringValue(mixed $value): string
    {
        if ($value === null) return 'null';
        if (is_bool($value)) return $value ? 'true' : 'false';
        if (is_scalar($value) || $value instanceof \Stringable) return (string)$value;
        return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?: get_debug_type($value);
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

        $headLength = (int)ceil($limit * 0.7);
        $tailLength = $limit - $headLength;
        $head = function_exists('mb_substr') ? mb_substr($value, 0, $headLength) : substr($value, 0, $headLength);
        $tail = $tailLength > 0
            ? (function_exists('mb_substr') ? mb_substr($value, -$tailLength) : substr($value, -$tailLength))
            : '';

        return $head . ' … +' . ($length - $limit) . ' chars … ' . $tail;
    }

    private function trimLines(string $value, int $limit): string
    {
        if ($limit < 1) {
            return '';
        }
        $lines = preg_split('/\R/u', $value) ?: [$value];
        $count = count($lines);
        if ($count <= $limit) {
            return $value;
        }

        $headCount = (int)ceil($limit / 2);
        $tailCount = $limit - $headCount;
        $head = array_slice($lines, 0, $headCount);
        $tail = $tailCount > 0 ? array_slice($lines, -$tailCount) : [];
        $hidden = $count - $limit;

        return implode(PHP_EOL, array_merge($head, ['… +' . $hidden . ' lines …'], $tail));
    }
}
