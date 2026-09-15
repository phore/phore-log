<?php

namespace Test;

use Phore\Log\Format\PhoreConsoleLogFormat;
use Phore\Log\LogLevelEnum;
use Phore\Log\LogRecord;
use Phore\Log\LogTypeEnum;
use PHPUnit\Framework\TestCase;

class ConsoleLoggerTest extends TestCase
{
    public function testScopedSuccessIsIndentedAndReadable(): void
    {
        $format = new PhoreConsoleLogFormat(false);
        $record = new LogRecord(microtime(true), LogLevelEnum::INFO, LogTypeEnum::SUCCESS, 'User saved', ['id' => 42], 'user.repository', 2, __FILE__, __LINE__);
        self::assertSame('    [repository] ✓ User saved  id=42', $format->format($record));
    }
}
