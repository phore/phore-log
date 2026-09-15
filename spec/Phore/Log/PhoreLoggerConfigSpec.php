<?php

namespace spec\Phore\Log;

use Phore\Log\LogLevelEnum;
use Phore\Log\PhoreLoggerConfig;
use PhpSpec\ObjectBehavior;

class PhoreLoggerConfigSpec extends ObjectBehavior
{
    function it_is_initializable(): void
    {
        $this->shouldHaveType(PhoreLoggerConfig::class);
    }

    function it_logs_debug_by_default(): void
    {
        $this->shouldLog('', LogLevelEnum::DEBUG)->shouldReturn(true);
    }

    function it_inherits_scope_levels_to_child_modules(): void
    {
        $this->setDefaultLevel(LogLevelEnum::INFO);
        $this->setScopeLevel('user', LogLevelEnum::DEBUG);

        $this->shouldLog('mail', LogLevelEnum::DEBUG)->shouldReturn(false);
        $this->shouldLog('user', LogLevelEnum::DEBUG)->shouldReturn(true);
        $this->shouldLog('user.repository', LogLevelEnum::DEBUG)->shouldReturn(true);
    }

    function it_prefers_the_most_specific_scope_rule(): void
    {
        $this->setDefaultLevel(LogLevelEnum::INFO);
        $this->setScopeLevel('user', LogLevelEnum::DEBUG);
        $this->setScopeLevel('user.repository', LogLevelEnum::WARNING);

        $this->shouldLog('user.validation', LogLevelEnum::DEBUG)->shouldReturn(true);
        $this->shouldLog('user.repository', LogLevelEnum::INFO)->shouldReturn(false);
        $this->shouldLog('user.repository', LogLevelEnum::WARNING)->shouldReturn(true);
    }
}
