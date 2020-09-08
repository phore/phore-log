# Phore log :: PSR-3 compatible logger

[![Actions Status](https://github.com/phore/phore-log/workflows/tests/badge.svg)](https://github.com/phore/phore-log/actions)

## Logger Usage



```
phore_log("something to log :message", ["message"=>"Hello"]);

phore_log()->setLogLevel(LogLevel::DEBUG);
phore_log()->setDriver(new PhoreCachedLoggerDriver());



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
| LogLevel::DEBUG       | 7    |


### Logging configuration

You can specify one or more logger with different log levels.

```
udp+syslog://<hostname>:<port>/<tag>?severity=4&
udp+syslogng://
file://stderr?severity=4
```



| Driver
