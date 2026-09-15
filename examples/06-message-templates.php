<?php

use Phore\Log\Driver\PhoreConsoleLoggerDriver;
use Phore\Log\PhoreLogger;

require __DIR__ . '/../vendor/autoload.php';

$log = new PhoreLogger(new PhoreConsoleLoggerDriver(colors: false));

$log->info('User {userId} scored {score:dec=2}', [
    'userId' => 42,
    'score' => 0.87654,
]);

$log->debug('Request finished in {duration:ms|dec=1}', [
    'duration' => 0.03245,
]);

$payload = "first line\nsecond line\nthird line\nfourth line\nfifth line\nsixth line\nlast line";
$log->detail("Payload:\n{payload}", ['payload' => $payload]);
$log->detail("Payload without automatic shortening:\n{payload:full}", ['payload' => $payload]);
$log->detail('Compact token: {token:trim=16}', ['token' => 'abcdefghijklmnopqrstuvwxyz0123456789']);

$log->detail('Payload file: {payload:json|file}', [
    'payload' => ['user' => ['id' => 42], 'active' => true],
]);

$object = new stdClass();
$object->id = 42;
$object->name = 'Alice';
$log->detail('Serialized object: {object:file}', ['object' => $object]);
