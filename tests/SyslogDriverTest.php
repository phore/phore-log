<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 27.11.19
 * Time: 19:58
 */

namespace Test;

use Phore\Log\Logger\PhoreSyslogLoggerDriver;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

/**
 * Class SyslogDriverTest
 * @package Test
 * @internal
 */
class SyslogDriverTest extends TestCase
{

    public function testBasicSetup()
    {
        $driver = new PhoreSyslogLoggerDriver("udp://localhost:4200?tag=someInstance");


        $this->assertEquals("127.0.0.1", $driver->getSyslogHostAddr());

    }

    public function testHostNotFoundSetup()
    {
        $driver = new PhoreSyslogLoggerDriver("udp://unknown_host:4200?tag=someInstance");

        $this->assertEquals(null, $driver->getSyslogHostAddr());
    }

}
