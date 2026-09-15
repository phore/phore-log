<?php

namespace Phore\Log\Format;

use Phore\Log\LogRecord;

class PhoreSyslogLogFormat implements PhoreLogFormat
{
    public function __construct(private array $options = ['facility' => 2, 'tag' => 'unnamed'])
    {
    }

    public function format(LogRecord $record): string
    {
        $pri = ((int)$this->options['facility'] * 8) + $record->level->severity();
        $scope = $record->scope === '' ? '' : '[' . $record->scope . '] ';
        $message = $scope . $record->interpolatedMessage();
        $line = sprintf('<%d>%s %s %s: %s', $pri, date('M d H:i:s'), gethostname(), $this->options['tag'], $message);
        return substr($line, 0, 1390);
    }
}
