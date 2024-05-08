<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 07.08.18
 * Time: 08:39
 */

namespace Phore\Log\Driver;


use Phore\Log\Format\PhoreLogFormat;
use Phore\Log\LogLevelEnum;

interface PhoreLoggerDriver
{
    public function log (LogLevelEnum $logLevel, string $file, int $lineNo, $message, $context = []);

    public function setMinSeverity(LogLevelEnum $logLevel);

    public function setFormatter(PhoreLogFormat $logFormat);
}
