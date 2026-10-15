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
    private bool $interactive;
    private bool $colorsEnabled;
    private int $activeLines = 0;

    public function __construct(string $target = 'php://stderr', ?bool $colors = null)
    {
        $this->stream = fopen($target, 'ab');
        if ($this->stream === false) {
            throw new \RuntimeException("Cannot open console target '$target'");
        }
        $this->interactive = function_exists('stream_isatty') && stream_isatty($this->stream);
        $colors ??= $this->interactive;
        $this->colorsEnabled = $colors;
        $this->formatter = new PhoreConsoleLogFormat($colors);
    }

    public function log(LogRecord $record): void
    {
        if ($record->level->severity() > $this->minLevel->severity()) {
            return;
        }
        $this->clearActive();
        fwrite($this->stream, $this->formatter->format($record) . PHP_EOL);
    }


    /**
     * Rendert eine Task-Zeile und ein temporäres Detail; ohne TTY append-only.
     *
     * @param list<string> $details Gesammelte Unterereignisse.
     */
    public function taskEvent(string $title, string $event, string $message, array $details): void
    {
        $this->clearActive();
        $status = match ($event) {
            'success' => '✓',
            'failure' => '✗',
            default => '→',
        };
        $color = match ($event) {
            'success' => '32',
            'failure' => '31',
            default => '36',
        };
        $label = $status . ' ' . $title;
        if ($event === 'success' || $event === 'failure') {
            $label .= ' ... ' . $message;
        } else {
            $label .= ' ... pending';
        }
        $line = $this->colorsEnabled ? "\\033[" . $color . "m" . $label . "\\033[0m" : $label;
        if ($this->interactive) {
            fwrite($this->stream, $line . PHP_EOL);
            $last = $event === 'failure' ? $details : array_slice($details, -1);
            foreach ($last as $index => $detail) {
                fwrite($this->stream, '  ' . ($index === count($last) - 1 ? '└' : '├') . '─ ' . $detail . PHP_EOL);
            }
            $this->activeLines = in_array($event, ['start', 'detail'], true) ? 1 + count($last) : 0;
            return;
        }
        if ($event === 'start' || $event === 'detail') {
            fwrite($this->stream, $event === 'start' ? $label . PHP_EOL : '  · ' . $message . PHP_EOL);
            return;
        }
        fwrite($this->stream, $label . PHP_EOL);
        if ($event === 'failure') {
            foreach ($details as $detail) {
                fwrite($this->stream, '  · ' . $detail . PHP_EOL);
            }
        }
    }

    private function clearActive(): void
    {
        if (!$this->interactive || $this->activeLines === 0) {
            return;
        }
        fwrite($this->stream, str_repeat("\\033[1A\\r\\033[2K", $this->activeLines));
        $this->activeLines = 0;
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
