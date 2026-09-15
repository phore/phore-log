<?php

use Phore\Log\LogLevelEnum;
use Phore\Log\PhoreLogger;
use Phore\Log\PhoreStopWatch;

PhoreStopWatch::__Init();

function phore_log($message = null, array $context = []): PhoreLogger
{
    $logger = PhoreLogger::GetInstance();
    if ($message !== null) {
        $logger->debug($message, $context);
    }
    return $logger;
}

function phore_loglevel_to_int(LogLevelEnum $logLevel): int
{
    return $logLevel->severity();
}
