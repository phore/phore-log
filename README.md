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

## Semantic console types

Besides the PSR-3 methods, Phore Log provides `step()`, `success()`, `result()`, `detail()`, `skip()` and `failure()`. These are presentation semantics, not additional severity levels: e.g. `success()` is an INFO record and `detail()` is DEBUG.

## URI configuration

```php
Phore\Log\PhoreLogger::Register(
    Phore\Log\PhoreLoggerFactory::BuildFromUri('def://stderr?severity=info')
);
```

Supported targets include `def://stderr`, `def://stdout`, `console://stderr`, `file:///path/to/file.log` and `syslog+udp://host:port`.

See `examples/01-console.php`, `examples/02-scoped-modules.php` and `examples/03-injected-child-logger.php` for complete examples.
