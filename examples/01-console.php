<?php

use Phore\Log\Driver\PhoreConsoleLoggerDriver;
use Phore\Log\PhoreLogger;

require dirname(__DIR__) . '/vendor/autoload.php';

$log = new PhoreLogger(new PhoreConsoleLoggerDriver());
$log->step('Load customer');
$log->success('Customer loaded', ['id' => 42]);
$log->warning('Invoice address is incomplete');
$log->result('Customer processing finished');
