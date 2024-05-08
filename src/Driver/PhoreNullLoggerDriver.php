<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 07.08.18
 * Time: 08:42
 */

namespace Phore\Log\Driver;


use Phore\Log\Format\PhoreLogFormat;
use Phore\Log\LogLevelEnum;

class PhoreNullLoggerDriver implements PhoreLoggerDriver
{

    public function log (LogLevelEnum $logLevel, string $file, int $lineNo, $message, $context = [])
    {
        // Do nothing
    }

    public function setMinSeverity(LogLevelEnum $logLevel)
    {
        // TODO: Implement setSeverity() method.
    }

    public function setFormatter(PhoreLogFormat $logFormat)
    {
        // TODO: Implement setFormatter() method.
    }
}
