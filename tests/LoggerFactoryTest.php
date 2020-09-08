<?php
namespace Test;


use Phore\Log\Logger\PhoreSyslogLoggerDriver;
use Phore\Log\PhoreLoggerFactory;
use PHPUnit\Framework\TestCase;

/**
 * Class LoggerFactoryTest
 * @package Test
 * @internal
 */
class LoggerFactoryTest extends TestCase
{

    public function testEmptyLogger()
    {
        $logger = PhoreLoggerFactory::BuildFromUri();

        self::assertEquals([], $logger->getDrivers());
        $logger->warning("send a warning");
    }


    public function testSyslogLogger()
    {
        $logger = PhoreLoggerFactory::BuildFromUri("syslog+udp://localhost:4200");

        $driver = $logger->getDrivers()[0];
        self::assertInstanceOf(PhoreSyslogLoggerDriver::class, $driver);
        /* @var $driver \Phore\Log\Logger\PhoreSyslogLoggerDriver */
        self::assertEquals("127.0.0.1", $driver->getSyslogHostAddr());
        $logger->warning("send a warning");
    }

    public function testInvalidLogger()
    {
        self::expectException(\InvalidArgumentException::class);
        PhoreLoggerFactory::BuildFromUri("unknown://some/path");
    }

}
