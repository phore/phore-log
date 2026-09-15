<?php

use Phore\Log\Driver\PhoreConsoleLoggerDriver;
use Phore\Log\PhoreLogger;

require __DIR__ . '/../vendor/autoload.php';

$log = new PhoreLogger(new PhoreConsoleLoggerDriver(colors: false));

$log->info('User {} scored {:dec=2}', [42, 0.87654]);
$log->debug('Imported {} records for {tenant}', [17, 'tenant' => 'acme', 'source' => 'csv']);
$log->debug('Request finished in {:ms|dec=1}', [0.03245]);
$log->debug('enabled={} disabled={} missing={} label={} empty={}', [true, false, null, 'ready', '']);

$payload = "first line\nsecond line\nthird line\nfourth line\nfifth line\nsixth line\nlast line";
$log->detail("Payload:\n{}", [$payload]);
$log->detail("Payload without automatic shortening:\n{:full}", [$payload]);
$log->detail('Compact token: {:trim=16}', ['abcdefghijklmnopqrstuvwxyz0123456789']);
$log->detail('Raw positional payload: {:file}', [$payload]);

$log->detail('Payload file: {payload}', ['payload:json|file' => ['user' => ['id' => 42], 'active' => true]]);

$object = new stdClass();
$object->id = 42;
$object->name = 'Alice';
$log->detail('Serialized object: {object}', ['object:file' => $object]);
