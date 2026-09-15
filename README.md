# Phore log :: PSR-3 compatible logger

Phore Log combines PSR-3 logging with readable console output for exploratory development and structured module scopes.

## Installation

```bash
composer require phore/log
```

Requires PHP 8.5 or newer.

## Console logging

```php
$log = new Phore\Log\PhoreLogger(new Phore\Log\Driver\PhoreConsoleLoggerDriver());
$log->step('Load customer');
$log->success('Customer loaded', ['id' => 42]);
$log->warning('Invoice address is incomplete');
```

Console output uses semantic symbols and ANSI colors when the output stream is a TTY. Other drivers receive the same structured record without console escape sequences.

## Message templates

For one or a few values, numeric context entries can be consumed positionally by `{}` placeholders from left to right:

```php
$log->info('User {} scored {:dec=2}', [42, 0.87654]);
$log->debug('Request finished in {:ms|dec=1}', [0.03245]);
```

Pass typed values directly to the logger. Do not pre-format booleans or nulls into strings such as `$flag ? 'true' : 'false'` or `$value ?? 'null'`. The logger renders booleans as `true` / `false`, `null` as `null`, normal strings in single quotes, and an empty string as `''`.

```php
$log->debug('enabled={} missing={} label={} empty={}', [true, null, 'ready', '']);
```

This renders the values unambiguously as `true`, `null`, `'ready'` and `''`.

Numeric and named context can be mixed. Numeric entries are consumed only by positional placeholders, while named entries can be referenced explicitly and unused named entries remain structured context:

```php
$log->debug('Imported {} records for {tenant}', [17, 'tenant' => 'acme', 'source' => 'csv']);
```

Named context values remain useful when the field name itself is important:

```php
$log->info('User {userId} scored {score:dec=2}', ['userId' => 42, 'score' => 0.87654, 'source' => 'api']);
```

Context keys used by placeholders are not repeated as trailing `key=value` fields.

Formatting can also be declared directly on a named context key:

```php
$log->debug('Request finished in {duration}', ['duration:ms|dec=1' => 0.03245]);
$log->detail('Payload: {payload}', ['payload:json|file' => $payload]);
```

Only the colon form is supported for context-key formats. An explicit format in the message template overrides the format declared on the context key, so `{payload:full}` can intentionally render a value inline even when the context contains `payload:file`.

Placeholder filters are written after a colon and can be combined with `|`:

- `{:dec=2}` / `{value:dec=2}` — fixed decimal places.
- `{:ms}` / `{duration:ms}` — seconds rendered as milliseconds.
- `{:ms|dec=1}` — milliseconds with explicit precision.
- `{:trim=80}` — explicit character budget while preserving beginning and end.
- `{:lines=5}` — explicit line budget while preserving beginning and end.
- `{:json}` — pretty JSON representation.
- `{:serialize}` — PHP serialized representation.
- `{:full}` — disable automatic shortening for this placeholder.
- `{:file}` — write the complete value to a temporary file and render only its absolute `file://` URI.

Long values are shortened automatically in console/default output. More than 5 lines or 240 characters are compacted while retaining both the beginning and end, with the omitted amount shown as `… +N lines …` or `… +N chars …`. The structured context remains complete.

For file placeholders, strings are written unchanged and unescaped, arrays are written as pretty multiline JSON, and objects are written with PHP `serialize()`. `{:json|file}` forces pretty JSON and `{:serialize|file}` forces serialized output. Files are named sequentially as `phore-log-000001.txt`, `phore-log-000002.txt`, and so on in the system temp directory. On the first file write of a new PHP process, leftover `phore-log-*.txt` files from the previous run are removed.

Literal braces can be escaped with `{{` and `}}`.

## Scoped child loggers

Child loggers inherit drivers, context and central configuration while adding a module scope:

```php
$log->setLogLevel(Phore\Log\LogLevelEnum::INFO);
$log->setScopeLevel('user', Phore\Log\LogLevelEnum::DEBUG);

$userLog = $log->scope('user');
$repositoryLog = $userLog->scope('repository');

$repositoryLog->debug('Load current user');
```

A level configured for `user` is inherited by `user.repository` and deeper scopes. More specific scope rules win. `user.*` is accepted as an explicit subtree rule.

## Context

```php
$userLog = $log->scope('user')->withContext(['userId' => 42]);
$userLog->success('User {userId} updated');
```

Context stays structured for non-console drivers and is inherited by child loggers.

For short-lived context, use `inContext()`. The callback receives a child logger and the parent logger is unchanged afterwards:

```php
$log->inContext(['userId' => 42], function (Phore\Log\PhoreLogger $log): void {
    $log->scope('user')->step('Load user {userId}');
    $log->scope('user')->success('User {userId} loaded');
});
```

## Failure buffer

A buffered console logger keeps recent log records in memory and stays silent until a configured failure level occurs. The buffered records are then replayed in order, followed by the triggering error. Afterwards the buffer starts a new cycle.

```php
$trace = Phore\Log\PhoreLogger::bufferedConsole(capacity: 50);
$trace->debug('Candidate A score {score}', ['score' => 0.71]);
$trace->step('Select best candidate');
$trace->error('Model resolution failed');
```

This is intended as a separate diagnostic logger for exploratory development, so verbose traces do not flood normal console output.

## Semantic console types

Besides the PSR-3 methods, Phore Log provides `step()`, `success()`, `result()`, `detail()`, `skip()` and `failure()`. These are presentation semantics, not additional severity levels: e.g. `success()` is an INFO record and `detail()` is DEBUG.

## URI configuration

```php
Phore\Log\PhoreLogger::Register(
    Phore\Log\PhoreLoggerFactory::BuildFromUri('def://stderr?severity=info')
);
```

Supported targets include `def://stderr`, `def://stdout`, `console://stderr`, `file:///path/to/file.log` and `syslog+udp://host:port`.

See `examples/01-console.php` through `examples/06-message-templates.php` for the complete example sequence.
