# Phore log :: PSR-3 compatible logger

[![Actions Status](https://github.com/phore/phore-log/workflows/tests/badge.svg)](https://github.com/phore/phore-log/actions)

- PSR-3 compliant logger
- Multiple targets (syslog, file, pipe) with individual configuration
- Quick configuration with single uri
- Multi-format support

## Installation

```bash
composer require phore/log
```

## Logger Usage

**Easy usage**
```php
phore_Log("Some log message"); // Debug message
phore_log("Value :val expected", ["val"=>"some unescaped value"]); // Auto escaping
phore_log()->emergency("Emergency Message");
```

## Configuration

**Global configuration**
```php
PhoreLogger::Register(PhoreLoggerFactory::BuildFromUri("syslog+udp://metrics.host.tld:4200?tag=server1"));
```

**Multi instance**
```
$logger = PhoreLoggerFactory::BuildFromUri();
```

### Logging
```
phore_log("something to log :message", ["message"=>"Hello"]);

phore_log()->setLogLevel(LogLevel::DEBUG);
phore_log()->emergency("emergency"); 

```

### LogLevel

| LogLevel              | Code |
|-----------------------|------|
| LogLevel::EMERGENCY   | 0    |
| LogLevel::ALERT       | 1    |
| LogLevel::CRITICAL    | 2    |
| LogLevel::ERROR       | 3    |
| LogLevel::WARNING     | 4    |
| LogLevel::NOTICE      | 5    |
| LogLevel::INFO        | 6    |
| LogLevel::DEBUG       | 7 (default)   |


### Logging configuration

You can specify one or more logger with different log levels.

```
syslog+udp://<hostname>:<port>/<tag>?severity=4&
syslogng+udp://
stderr://?severity=4
stdout://?severity=4
file:///var/log/xy.log?severity=4
```
