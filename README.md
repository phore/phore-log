# Phore log :: PSR-3 compatible logger

## Logger Usage



```
phore_log("something to log :message", ["message"=>"Hello"]);

phore_log()->setLogLevel(LogLevel::DEBUG);
phore_log()->setDriver(new PhoreEchoLoggerDriver());

phore_log()->emergency("emergency"); 

```
