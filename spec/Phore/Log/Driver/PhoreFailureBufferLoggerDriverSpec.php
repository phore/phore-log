<?php

namespace spec\Phore\Log\Driver;

use Phore\Log\Driver\PhoreCachedLoggerDriver;
use Phore\Log\Driver\PhoreFailureBufferLoggerDriver;
use Phore\Log\LogLevelEnum;
use Phore\Log\PhoreLogger;
use PhpSpec\ObjectBehavior;

class PhoreFailureBufferLoggerDriverSpec extends ObjectBehavior
{
    function it_is_initializable(): void
    {
        $this->beConstructedWith(new PhoreCachedLoggerDriver());
        $this->shouldHaveType(PhoreFailureBufferLoggerDriver::class);
    }

    function it_replays_buffered_records_when_an_error_occurs(): void
    {
        $target = new PhoreCachedLoggerDriver();
        $logger = new PhoreLogger(new PhoreFailureBufferLoggerDriver($target));

        $logger->debug('candidate A');
        $logger->info('candidate selected');

        if ($target->getLogs() !== []) {
            throw new \RuntimeException('Buffered records were emitted before a failure');
        }

        $logger->error('model failed');

        $logs = $target->getLogs();
        if (count($logs) !== 3) {
            throw new \RuntimeException('Expected buffered records and the triggering error');
        }
        if (!str_contains($logs[0], 'candidate A') || !str_contains($logs[2], 'model failed')) {
            throw new \RuntimeException('Buffered records were not replayed in order');
        }
    }

    function it_keeps_only_the_configured_number_of_records(): void
    {
        $target = new PhoreCachedLoggerDriver();
        $driver = new PhoreFailureBufferLoggerDriver($target, LogLevelEnum::ERROR, 2);
        $logger = new PhoreLogger($driver);

        $logger->debug('one');
        $logger->debug('two');
        $logger->debug('three');
        $logger->error('failed');

        $logs = $target->getLogs();
        if (count($logs) !== 3 || str_contains(implode('\n', $logs), 'one')) {
            throw new \RuntimeException('Failure buffer did not enforce its capacity');
        }
    }
}
