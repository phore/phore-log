# Phore log :: PSR-3 compatible logger

## Logger Usage



```
phore_log("something to log :message", ["message"=>"Hello"]);

phore_log()->setLogLevel(LogLevel::DEBUG);
phore_log()->setDriver(new PhoreCachedLoggerDriver());



phore_log()->emergency("emergency"); 

```
