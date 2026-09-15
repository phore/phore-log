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

    function it_interpolates_placeholders_without_repeating_used_context(): void
    {
        $record = new LogRecord(
            microtime(true),
            LogLevelEnum::INFO,
            LogTypeEnum::MESSAGE,
            'User {userId} loaded',
            ['userId' => 42, 'source' => 'api'],
            '',
            0,
            __FILE__,
            __LINE__
        );

        $this->format($record)->shouldReturn('ℹ User 42 loaded  source=api');
    }

    function it_formats_decimal_places_and_milliseconds(): void
    {
        $record = new LogRecord(
            microtime(true),
            LogLevelEnum::INFO,
            LogTypeEnum::MESSAGE,
            'Score {score:dec=2}, duration {duration:ms|dec=1}',
            ['score' => 0.87654, 'duration' => 0.03245],
            '',
            0,
            __FILE__,
            __LINE__
        );

        $this->format($record)->shouldReturn('ℹ Score 0.88, duration 32.5 ms');
    }

    function it_trims_long_multiline_placeholders_with_head_and_tail(): void
    {
        $record = new LogRecord(
            microtime(true),
            LogLevelEnum::DEBUG,
            LogTypeEnum::DETAIL,
            "Payload:\n{payload}",
            ['payload' => "line1\nline2\nline3\nline4\nline5\nline6\nline7"],
            '',
            0,
            __FILE__,
            __LINE__
        );

        $this->format($record)->shouldReturn("· Payload:\nline1\nline2\nline3\n… +2 lines …\nline6\nline7");
    }

    function it_can_disable_automatic_trimming_per_placeholder(): void
    {
        $record = new LogRecord(
            microtime(true),
            LogLevelEnum::DEBUG,
            LogTypeEnum::DETAIL,
            '{payload:full}',
            ['payload' => "line1\nline2\nline3\nline4\nline5\nline6"],
            '',
            0,
            __FILE__,
            __LINE__
        );

        $this->format($record)->shouldReturn("· line1\nline2\nline3\nline4\nline5\nline6");
    }

    function it_can_trim_a_placeholder_to_an_explicit_character_budget(): void
    {
        $record = new LogRecord(
            microtime(true),
            LogLevelEnum::INFO,
            LogTypeEnum::MESSAGE,
            '{text:trim=10}',
            ['text' => 'abcdefghijklmnopqrstuvwxyz'],
            '',
            0,
            __FILE__,
            __LINE__
        );

        $this->format($record)->shouldReturn('ℹ abcdefg … +16 chars … xyz');
    }

    function it_writes_pretty_multiline_json_to_a_file_placeholder(): void
    {
        $record = new LogRecord(
            microtime(true),
            LogLevelEnum::DEBUG,
            LogTypeEnum::DETAIL,
            'Payload {payload:json|file}',
            ['payload' => ['user' => ['id' => 42], 'active' => true]],
            '',
            0,
            __FILE__,
            __LINE__
        );

        $line = (new PhoreConsoleLogFormat(false))->format($record);
        if (!preg_match('#file://(/[^\s]+)$#', $line, $match)) {
            throw new \RuntimeException('Expected absolute file URI in console output');
        }

        $contents = file_get_contents($match[1]);
        $expected = "{\n    \"user\": {\n        \"id\": 42\n    },\n    \"active\": true\n}";
        if ($contents !== $expected) {
            throw new \RuntimeException('Expected pretty multiline JSON in placeholder file');
        }
    }
}
