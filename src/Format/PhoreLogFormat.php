<?php


namespace Phore\Log\Format;


interface PhoreLogFormat
{

    public function format(int $severity, string $file, int $lineNo, ...$params) : string;

}
