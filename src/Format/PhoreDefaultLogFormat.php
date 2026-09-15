<?php

namespace Phore\Log\Format;

use Phore\Log\LogRecord;
use Phore\Log\PhoreStopWatch;

class PhoreDefaultLogFormat implements PhoreLogFormat
{
    public function __construct(
        private bool $level = true,
        private bool $time = true,
        private bool $lineNo = true,
        private int $placeholderMaxLines = 5,
        private int $placeholderMaxChars = 240
    ) {
    }

    public function format(LogRecord $record): string
    {
        $line = '';
        if ($this->level) {
            $line .= '[' . $record->level->name . ']';
        }
        if ($this->time) {
            $line .= '[+' . str_pad(number_format(PhoreStopWatch::GetScriptRunTime(), 3, '.', ''), 7, ' ', STR_PAD_LEFT) . ']';
        }
        if ($this->lineNo) {
            $line .= '[:' . str_pad((string)$record->line, 3, ' ', STR_PAD_LEFT) . ']';
        }
        if ($record->scope !== '') {
            $line .= '[' . $record->scope . ']';
        }
        return $line . ' ' . $record->interpolatedMessage($this->placeholderMaxLines, $this->placeholderMaxChars);
    }
}
