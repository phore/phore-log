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
        $record = new LogRecord(microtime(true), LogLevelEnum::INFO, LogTypeEnum::SUCCESS, 'User saved', ['id' => 42], 'user.repository', 2, __FILE__, __LINE__);
        $this->format($record)->shouldReturn('    [repository] ✓ User saved  id=42');
    }

    function it_aligns_explicit_multiline_content_to_the_message_column(): void
    {
        $record = new LogRecord(microtime(true), LogLevelEnum::INFO, LogTypeEnum::SUCCESS, "First line\nSecond line", [], 'user.repository', 2, __FILE__, __LINE__);
        $continuation = str_repeat(' ', 19);
        $this->format($record)->shouldReturn("    [repository] ✓ First line\n{$continuation}Second line");
    }

    function it_wraps_long_lines_and_aligns_continuations_to_the_message_column(): void
    {
        $this->beConstructedWith(false, 5, 240, 32);
        $record = new LogRecord(microtime(true), LogLevelEnum::INFO, LogTypeEnum::MESSAGE, 'This is a long message that should wrap cleanly at the configured console width', [], '', 0, __FILE__, __LINE__);
        $this->format($record)->shouldReturn("ℹ This is a long message that\n  should wrap cleanly at the\n  configured console width");
    }

    function it_interpolates_placeholders_without_repeating_used_context(): void
    {
        $record = new LogRecord(microtime(true), LogLevelEnum::INFO, LogTypeEnum::MESSAGE, 'User {userId} loaded', ['userId' => 42, 'source' => 'api'], '', 0, __FILE__, __LINE__);
        $this->format($record)->shouldReturn('ℹ User 42 loaded  source=api');
    }

    function it_supports_positional_placeholders_from_numeric_context_values(): void
    {
        $record = new LogRecord(microtime(true), LogLevelEnum::INFO, LogTypeEnum::MESSAGE, 'User {} scored {:dec=2}', [42, 0.87654], '', 0, __FILE__, __LINE__);
        $this->format($record)->shouldReturn('ℹ User 42 scored 0.88');
    }

    function it_mixes_positional_placeholders_with_named_structured_context(): void
    {
        $record = new LogRecord(microtime(true), LogLevelEnum::INFO, LogTypeEnum::MESSAGE, 'Imported {} records for {tenant}', [17, 'tenant' => 'acme', 'source' => 'csv'], '', 0, __FILE__, __LINE__);
        $this->format($record)->shouldReturn('ℹ Imported 17 records for acme  source=csv');
    }

    function it_formats_decimal_places_and_milliseconds(): void
    {
        $record = new LogRecord(microtime(true), LogLevelEnum::INFO, LogTypeEnum::MESSAGE, 'Score {score:dec=2}, duration {duration:ms|dec=1}', ['score' => 0.87654, 'duration' => 0.03245], '', 0, __FILE__, __LINE__);
        $this->format($record)->shouldReturn('ℹ Score 0.88, duration 32.5 ms');
    }

    function it_uses_filters_declared_on_context_keys(): void
    {
        $record = new LogRecord(microtime(true), LogLevelEnum::INFO, LogTypeEnum::MESSAGE, 'Duration {duration}', ['duration:ms|dec=1' => 0.03245], '', 0, __FILE__, __LINE__);
        $this->format($record)->shouldReturn('ℹ Duration 32.5 ms');
    }

    function it_lets_explicit_placeholder_filters_override_context_key_filters(): void
    {
        $record = new LogRecord(microtime(true), LogLevelEnum::DEBUG, LogTypeEnum::DETAIL, '{payload:full}', ['payload:trim=5' => 'abcdefghij'], '', 0, __FILE__, __LINE__);
        $this->format($record)->shouldReturn('· abcdefghij');
    }

    function it_trims_long_multiline_placeholders_with_head_and_tail(): void
    {
        $record = new LogRecord(microtime(true), LogLevelEnum::DEBUG, LogTypeEnum::DETAIL, "Payload:\n{payload}", ['payload' => "line1\nline2\nline3\nline4\nline5\nline6\nline7"], '', 0, __FILE__, __LINE__);
        $this->format($record)->shouldReturn("· Payload:\n  line1\n  line2\n  line3\n  … +2 lines …\n  line6\n  line7");
    }

    function it_can_disable_automatic_trimming_per_placeholder(): void
    {
        $record = new LogRecord(microtime(true), LogLevelEnum::DEBUG, LogTypeEnum::DETAIL, '{payload:full}', ['payload' => "line1\nline2\nline3\nline4\nline5\nline6"], '', 0, __FILE__, __LINE__);
        $this->format($record)->shouldReturn("· line1\n  line2\n  line3\n  line4\n  line5\n  line6");
    }

    function it_can_trim_a_placeholder_to_an_explicit_character_budget(): void
    {
        $record = new LogRecord(microtime(true), LogLevelEnum::INFO, LogTypeEnum::MESSAGE, '{text:trim=10}', ['text' => 'abcdefghijklmnopqrstuvwxyz'], '', 0, __FILE__, __LINE__);
        $this->format($record)->shouldReturn('ℹ abcdefg … +16 chars … xyz');
    }

    function it_writes_pretty_multiline_json_to_a_file_placeholder(): void
    {
        $record = new LogRecord(microtime(true), LogLevelEnum::DEBUG, LogTypeEnum::DETAIL, 'Payload {payload}', ['payload:json|file' => ['user' => ['id' => 42], 'active' => true]], '', 0, __FILE__, __LINE__);
        $line = (new PhoreConsoleLogFormat(false))->format($record);
        if (!preg_match('#file://(/[^\s]+)$#', $line, $match)) throw new \RuntimeException('Expected absolute file URI in console output');
        $contents = file_get_contents($match[1]);
        $expected = "{\n    \"user\": {\n        \"id\": 42\n    },\n    \"active\": true\n}";
        if ($contents !== $expected) throw new \RuntimeException('Expected pretty multiline JSON in placeholder file');
    }

    function it_serializes_objects_when_written_directly_to_a_file_placeholder(): void
    {
        $payload = new \stdClass();
        $payload->id = 42;
        $payload->name = 'Alice';
        $record = new LogRecord(microtime(true), LogLevelEnum::DEBUG, LogTypeEnum::DETAIL, 'Payload {payload}', ['payload:file' => $payload], '', 0, __FILE__, __LINE__);
        $line = (new PhoreConsoleLogFormat(false))->format($record);
        if (!preg_match('#file://(/[^\s]+)$#', $line, $match)) throw new \RuntimeException('Expected absolute file URI in console output');
        $restored = unserialize((string)file_get_contents($match[1]), ['allowed_classes' => [\stdClass::class]]);
        if (!$restored instanceof \stdClass || $restored->id !== 42 || $restored->name !== 'Alice') throw new \RuntimeException('Expected serialized object in placeholder file');
    }

    function it_writes_a_positional_value_to_a_file_placeholder(): void
    {
        $record = new LogRecord(microtime(true), LogLevelEnum::DEBUG, LogTypeEnum::DETAIL, 'Payload {:file}', ["first\nsecond"], '', 0, __FILE__, __LINE__);
        $line = (new PhoreConsoleLogFormat(false))->format($record);
        if (!preg_match('#file://(/[^\s]+)$#', $line, $match)) throw new \RuntimeException('Expected absolute file URI in console output');
        if (file_get_contents($match[1]) !== "first\nsecond") throw new \RuntimeException('Expected complete positional payload in placeholder file');
    }
}
