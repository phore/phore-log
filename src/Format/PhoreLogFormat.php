<?php

namespace Phore\Log\Format;

use Phore\Log\LogRecord;

interface PhoreLogFormat
{
    public function format(LogRecord $record): string;
}
