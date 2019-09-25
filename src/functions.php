<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 07.08.18
 * Time: 08:22
 */


\Phore\Log\PhoreStopWatch::__Init();



function phore_log ($message=null, array $context = []) : \Phore\Log\PhoreLogger
{
    $logger = \Phore\Log\PhoreLogger::GetInstance();
    if ($message !== null)
        $logger->_log(\Psr\Log\LogLevel::DEBUG, $message, $context);
    return $logger;
}
