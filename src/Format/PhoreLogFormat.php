<?php


namespace Phore\Log\Format;


use Phore\Log\LogLevelEnum;

interface PhoreLogFormat
{

    public function format(LogLevelEnum $level, string $file, int $lineNo, string $message, array $context = []) : string;

}
