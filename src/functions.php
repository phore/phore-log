<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 07.08.18
 * Time: 08:22
 */


\Phore\Log\PhoreStopWatch::__Init();



function phore_log (...$params) : \Phore\Log\PhoreLog
{
    $logger = \Phore\Log\PhoreLog::GetInstance();

    if (count($params) > 0 && $logger->getVerbosity() >= \Phore\Log\PhoreLog::LOG_DEBUG) {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        $logger->log(\Phore\Log\PhoreLog::LOG_DEBUG, $backtrace[0]["file"], $backtrace[0]["line"], ...$params);
    }
    return $logger;
}
