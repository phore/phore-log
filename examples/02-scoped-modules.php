<?php

use Phore\Log\Driver\PhoreConsoleLoggerDriver;
use Phore\Log\LogLevelEnum;
use Phore\Log\PhoreLogger;

require dirname(__DIR__) . '/vendor/autoload.php';

$log = new PhoreLogger(new PhoreConsoleLoggerDriver());
$log->setLogLevel(LogLevelEnum::INFO);
$log->setScopeLevel('user', LogLevelEnum::DEBUG);

$userLog = $log->scope('user');
$repositoryLog = $userLog->scope('repository');

$userLog->step('Update user');
$repositoryLog->debug('Load current record');
$repositoryLog->success('Record loaded');
$userLog->success('User updated');
