<?php

namespace Test;

use Phore\Log\Driver\PhoreCachedLoggerDriver;
use Phore\Log\LogLevelEnum;
use Phore\Log\PhoreLogger;
use PHPUnit\Framework\TestCase;

class LoggerTest extends TestCase
{
    public function testLoggingDefaultLogLevelDebug(): void
    {
        $logger = new PhoreLogger($driver = new PhoreCachedLoggerDriver());
        $logger->warning('Warn');
        $logger->debug('debug');
        self::assertCount(2, $driver->getLogs());
    }

    public function testScopedLevelsAreInheritedByChildren(): void
    {
        $logger = new PhoreLogger($driver = new PhoreCachedLoggerDriver());
        $logger->setLogLevel(LogLevelEnum::INFO);
        $user = $logger->scope('user');
        $repository = $user->scope('repository');

        $repository->debug('hidden');
        $logger->setScopeLevel('user', LogLevelEnum::DEBUG);
        $repository->debug('visible');

        self::assertCount(1, $driver->getLogs());
        self::assertStringContainsString('[user.repository]', $driver->getLogs()[0]);
    }

    public function testContextIsInherited(): void
    {
        $logger = (new PhoreLogger($driver = new PhoreCachedLoggerDriver()))
            ->withContext(['requestId' => 'r-1']);
        $logger->scope('user')->info('Request {requestId}');
        self::assertStringContainsString('Request r-1', $driver->getLogs()[0]);
    }
}
