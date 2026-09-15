<?php

use Phore\Log\Driver\PhoreConsoleLoggerDriver;
use Phore\Log\PhoreLogger;

require dirname(__DIR__) . '/vendor/autoload.php';

$rootLog = new PhoreLogger(new PhoreConsoleLoggerDriver());
$userLog = $rootLog->scope('user')->withContext(['userId' => 42]);

$service = new class($userLog) {
    public function __construct(private PhoreLogger $log) {}

    public function update(): void
    {
        $this->log->step('Validate user {userId}');
        $this->log->scope('repository')->detail('Persist model');
        $this->log->success('User {userId} updated');
    }
};

$service->update();
