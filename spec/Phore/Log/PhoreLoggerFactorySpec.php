<?php

namespace spec\Phore\Log;

use Phore\Log\Driver\PhoreSyslogLoggerDriver;
use Phore\Log\PhoreLoggerFactory;
use PhpSpec\ObjectBehavior;

class PhoreLoggerFactorySpec extends ObjectBehavior
{
    function it_is_initializable(): void
    {
        $this->shouldHaveType(PhoreLoggerFactory::class);
    }

    function it_builds_an_empty_logger_without_a_uri(): void
    {
        $logger = PhoreLoggerFactory::BuildFromUri();
        if ($logger->getDrivers() !== []) {
            throw new \RuntimeException('Expected logger without drivers');
        }
    }

    function it_builds_a_syslog_driver(): void
    {
        $driver = PhoreLoggerFactory::BuildFromUri('syslog+udp://localhost:4200')->getDrivers()[0] ?? null;
        if (!$driver instanceof PhoreSyslogLoggerDriver || $driver->getSyslogHostAddr() !== '127.0.0.1') {
            throw new \RuntimeException('Expected localhost syslog driver');
        }
    }

    function it_rejects_unknown_driver_schemes(): void
    {
        try {
            PhoreLoggerFactory::BuildFromUri('unknown://some/path');
        } catch (\InvalidArgumentException) {
            return;
        }
        throw new \RuntimeException('Expected InvalidArgumentException');
    }
}
