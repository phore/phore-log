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
        $config = new PhoreLoggerConfig();
        if (!$config->shouldLog('', LogLevelEnum::DEBUG)) {
            throw new \RuntimeException('DEBUG should be enabled by default');
        }
    }

    function it_inherits_scope_levels_to_child_modules(): void
    {
        $config = new PhoreLoggerConfig();
        $config->setDefaultLevel(LogLevelEnum::INFO);
        $config->setScopeLevel('user', LogLevelEnum::DEBUG);

        if ($config->shouldLog('mail', LogLevelEnum::DEBUG)) {
            throw new \RuntimeException('Unconfigured module should inherit INFO');
        }
        if (!$config->shouldLog('user', LogLevelEnum::DEBUG)) {
            throw new \RuntimeException('Configured module should log DEBUG');
        }
        if (!$config->shouldLog('user.repository', LogLevelEnum::DEBUG)) {
            throw new \RuntimeException('Child module should inherit DEBUG');
        }
    }

    function it_prefers_the_most_specific_scope_rule(): void
    {
        $config = new PhoreLoggerConfig();
        $config->setDefaultLevel(LogLevelEnum::INFO);
        $config->setScopeLevel('user', LogLevelEnum::DEBUG);
        $config->setScopeLevel('user.repository', LogLevelEnum::WARNING);

        if (!$config->shouldLog('user.validation', LogLevelEnum::DEBUG)) {
            throw new \RuntimeException('Sibling scope should keep inherited DEBUG');
        }
        if ($config->shouldLog('user.repository', LogLevelEnum::INFO)) {
            throw new \RuntimeException('More specific WARNING rule should hide INFO');
        }
        if (!$config->shouldLog('user.repository', LogLevelEnum::WARNING)) {
            throw new \RuntimeException('More specific WARNING rule should show WARNING');
        }
    }
}
