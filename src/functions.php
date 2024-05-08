<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 07.08.18
 * Time: 08:22
 */


use Psr\Log\LogLevel;

\Phore\Log\PhoreStopWatch::__Init();



function phore_log ($message=null, array $context = []) : \Phore\Log\PhoreLogger
{
    $logger = \Phore\Log\PhoreLogger::GetInstance();
    if ($message !== null)
        $logger->_log(\Phore\Log\LogLevelEnum::DEBUG, $message, $context);
    return $logger;
}

function phore_loglevel_to_int(\Phore\Log\LogLevelEnum $logLevel) : int
{
    $map = [
        \Phore\Log\LogLevelEnum::SUCCESS->value => 3, // Same as Error
        \Phore\Log\LogLevelEnum::EMERGENCY->value => 0,
        \Phore\Log\LogLevelEnum::ALERT->value => 1,
        \Phore\Log\LogLevelEnum::CRITICAL->value => 2,
        \Phore\Log\LogLevelEnum::ERROR ->value=> 3,
        \Phore\Log\LogLevelEnum::WARNING->value => 4,
        \Phore\Log\LogLevelEnum::NOTICE ->value=> 5,
        \Phore\Log\LogLevelEnum::INFO ->value=> 6,
        \Phore\Log\LogLevelEnum::DEBUG->value => 7
    ];

    return $map[$logLevel->value];
}
