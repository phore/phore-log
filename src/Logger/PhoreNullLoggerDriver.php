<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 07.08.18
 * Time: 08:42
 */

namespace Phore\Log\Logger;


class PhoreNullLoggerDriver implements PhoreLoggerDriver
{

    public function log(int $severity, string $file, int $lineNo, ...$params)
    {
        // Do nothing
    }
}
