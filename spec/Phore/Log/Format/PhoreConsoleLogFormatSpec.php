<?php

namespace spec\Phore\Log\Format;

use Phore\Log\Format\PhoreConsoleLogFormat;
use Phore\Log\LogLevelEnum;
use Phore\Log\LogRecord;
use Phore\Log\LogTypeEnum;
use PhpSpec\ObjectBehavior;

class PhoreConsoleLogFormatSpec extends ObjectBehavior
{
    function let(): void
    {
        $this->beConstructedWith(false);
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(PhoreConsoleLogFormat::class);
    }

    function it_indents_scoped_success_messages(): void
    {
        $record = new LogRecord(
            microtime(true),
            LogLevelEnum::INFO,
            LogTypeEnum::SUCCESS,
            'User saved',
            ['id' => 42],
            'user.repository',
            2,
            __FILE__,
            __LINE__
        );

        $this->format($record)->shouldReturn('    [repository] ✓ User saved  id=42');
    }
}
