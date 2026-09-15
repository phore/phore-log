<?php

use Phore\Log\PhoreLogger;

require __DIR__ . '/../vendor/autoload.php';

$trace = PhoreLogger::bufferedConsole(capacity: 50);

$trace->debug('Candidate A score {score}', ['score' => 0.71]);
$trace->debug('Candidate B score {score}', ['score' => 0.84]);
$trace->step('Select best candidate');

// Nothing has been printed yet. The buffered trace is replayed when the error occurs.
$trace->error('Model resolution failed');
