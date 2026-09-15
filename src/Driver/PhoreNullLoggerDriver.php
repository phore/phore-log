<?php

namespace Phore\Log\Driver;

use Phore\Log\Format\PhoreLogFormat;
use Phore\Log\LogLevelEnum;
use Phore\Log\LogRecord;

class PhoreNullLoggerDriver implements PhoreLoggerDriver
{
    public function log(LogRecord $record): void {}
    public function setMinSeverity(LogLevelEnum $logLevel): void {}
    public function setFormatter(PhoreLogFormat $logFormat): void {}
}
