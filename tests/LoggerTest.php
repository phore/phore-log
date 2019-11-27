<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 27.11.19
 * Time: 19:44
 */

namespace Test;


use Phore\Log\Logger\PhoreCachedLoggerDriver;
use Phore\Log\PhoreLogger;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

/**
 * Class LoggerTest
 * @package Test
 * @internal
 */
class LoggerTest extends TestCase
{


    public function testLoggingDefaultLogLevelDebug()
    {
        $logger = new PhoreLogger($driver = new PhoreCachedLoggerDriver());


        $logger->warning("Warn");
        $logger->debug("debug");

        print_r ($driver->getLogs());

        $this->assertEquals(2, count ($driver->getLogs()));

    }

    public function testLoggingEmergencyLoglevel()
    {
        $logger = new PhoreLogger($driver = new PhoreCachedLoggerDriver());
        $logger->setLogLevel(LogLevel::EMERGENCY);

        $logger->emergency("Warn");
        $logger->debug("debug");

        print_r ($driver->getLogs());

        $this->assertEquals(1, count ($driver->getLogs()));

    }



}
