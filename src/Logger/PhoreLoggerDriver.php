<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 07.08.18
 * Time: 08:39
 */

namespace Phore\Log\Logger;


use Phore\Log\Format\PhoreLogFormat;

interface PhoreLoggerDriver
{
    public function log (int $severity, string $file, int $lineNo, ...$params);

    public function setSeverity(int $severity);

    public function setFormatter(PhoreLogFormat $logFormat);
}
