<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 07.08.18
 * Time: 08:39
 */

namespace Phore\Log\Logger;


interface PhoreLogger
{
    public function log (int $severity, string $file, int $lineNo, ...$params);
}
