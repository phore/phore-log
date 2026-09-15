<?php

namespace spec\Phore\Log;

use Phore\Log\Driver\PhoreCachedLoggerDriver;
use Phore\Log\LogLevelEnum;
use Phore\Log\PhoreLogger;
use PhpSpec\ObjectBehavior;

class PhoreLoggerSpec extends ObjectBehavior
{
    private PhoreCachedLoggerDriver $driver;

    function let(): void
    {
        $this->driver = new PhoreCachedLoggerDriver();
        $this->beConstructedWith($this->driver);
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(PhoreLogger::class);
    }

    function it_logs_warning_and_debug_by_default(): void
    {
        $this->warning('Warn');
        $this->debug('debug');

        if (count($this->driver->getLogs()) !== 2) {
            throw new \RuntimeException('Expected two log records');
        }
    }

    function it_shares_scope_configuration_with_existing_children(): void
    {
        $logger = new PhoreLogger($driver = new PhoreCachedLoggerDriver());
        $logger->setLogLevel(LogLevelEnum::INFO);
        $repository = $logger->scope('user')->scope('repository');

        $repository->debug('hidden');
        $logger->setScopeLevel('user', LogLevelEnum::DEBUG);
        $repository->debug('visible');

        $logs = $driver->getLogs();
        if (count($logs) !== 1 || !str_contains($logs[0], '[user.repository]')) {
            throw new \RuntimeException('Child logger did not inherit the updated scope configuration');
        }
    }

    function it_inherits_context_into_child_scopes(): void
    {
        $logger = (new PhoreLogger($driver = new PhoreCachedLoggerDriver()))
            ->withContext(['requestId' => 'r-1']);

        $logger->scope('user')->info('Request {requestId}');

        if (!str_contains($driver->getLogs()[0] ?? '', 'Request r-1')) {
            throw new \RuntimeException('Child logger did not inherit context');
        }
    }

    function it_applies_context_only_for_the_callback(): void
    {
        $logger = new PhoreLogger($driver = new PhoreCachedLoggerDriver());

        $logger->inContext(['userId' => 42], function (PhoreLogger $log): void {
            $log->info('Inside {userId}');
        });
        $logger->info('Outside {userId}', ['userId' => 'none']);

        $logs = $driver->getLogs();
        if (!str_contains($logs[0] ?? '', 'Inside 42')) {
            throw new \RuntimeException('Temporary context was not applied inside the callback');
        }
        if (!str_contains($logs[1] ?? '', 'Outside none')) {
            throw new \RuntimeException('Temporary context leaked outside the callback');
        }
    }
}
