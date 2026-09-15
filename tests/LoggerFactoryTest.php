<?php

namespace Test;

use Phore\Log\Driver\PhoreSyslogLoggerDriver;
use Phore\Log\PhoreLoggerFactory;
use PHPUnit\Framework\TestCase;

class LoggerFactoryTest extends TestCase
{
    public function testEmptyLogger(): void
    {
        self::assertSame([], PhoreLoggerFactory::BuildFromUri()->getDrivers());
    }

    public function testSyslogLogger(): void
    {
        $driver = PhoreLoggerFactory::BuildFromUri('syslog+udp://localhost:4200')->getDrivers()[0];
        self::assertInstanceOf(PhoreSyslogLoggerDriver::class, $driver);
        self::assertSame('127.0.0.1', $driver->getSyslogHostAddr());
    }

    public function testInvalidLogger(): void
    {
        self::expectException(\InvalidArgumentException::class);
        PhoreLoggerFactory::BuildFromUri('unknown://some/path');
    }
}
