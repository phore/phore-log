<?php

use Phore\Log\Driver\PhoreConsoleLoggerDriver;
use Phore\Log\PhoreLogger;

require __DIR__ . '/../vendor/autoload.php';

$log = new PhoreLogger(new PhoreConsoleLoggerDriver());

$log->inContext(['userId' => 42], function (PhoreLogger $log): void {
    $log->scope('user')->step('Load user {userId}');
    $log->scope('user')->success('User {userId} loaded');
});

$log->info('The temporary user context is gone again');
