# Phore log :: PSR-3 compatible logger

[![Actions Status](https://github.com/phore/phore-log/workflows/tests/badge.svg)](https://github.com/phore/phore-log/actions)

## Logger Usage



```
phore_log("something to log :message", ["message"=>"Hello"]);

phore_log()->setLogLevel(LogLevel::DEBUG);
phore_log()->setDriver(new PhoreCachedLoggerDriver());



phore_log()->emergency("emergency"); 

```
