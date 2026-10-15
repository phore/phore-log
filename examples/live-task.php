<?php

use Phore\Log\PhoreLogger;
use Phore\Log\Driver\PhoreConsoleLoggerDriver;

$logger = new PhoreLogger(new PhoreConsoleLoggerDriver('php://temp', false));
$task = $logger->start('Bearbeite index.md');
$task->detail('Generiere');
$task->success();
