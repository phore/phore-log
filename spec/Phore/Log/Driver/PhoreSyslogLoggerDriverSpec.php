<?php

namespace spec\Phore\Log\Driver;

use Phore\Log\Driver\PhoreSyslogLoggerDriver;
use PhpSpec\ObjectBehavior;

class PhoreSyslogLoggerDriverSpec extends ObjectBehavior
{
    function let(): void
    {
        $this->beConstructedWith('udp://localhost:4200?tag=someInstance');
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(PhoreSyslogLoggerDriver::class);
    }

    function it_resolves_localhost(): void
    {
        $this->getSyslogHostAddr()->shouldReturn('127.0.0.1');
    }
}
