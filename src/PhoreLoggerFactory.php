<?php

namespace Phore\Log;

use Phore\Log\Driver\PhoreConsoleLoggerDriver;
use Phore\Log\Driver\PhoreEchoLoggerDriver;
use Phore\Log\Driver\PhoreSyslogLoggerDriver;

class PhoreLoggerFactory
{
    public static function BuildFromUri(string $uri = ''): PhoreLogger
    {
        $instance = new PhoreLogger();
        foreach (explode(';', $uri) as $driverUri) {
            if (trim($driverUri) === '') continue;
            $parsed = phore_parse_url($driverUri);
            $severity = LogLevelEnum::coerce($parsed->getQueryVal('severity', 7));
            $driver = match ($parsed->scheme) {
                'def', 'console' => self::consoleDriver($parsed->host, $uri),
                'file' => new PhoreEchoLoggerDriver($parsed->path),
                'udp', 'syslog+udp' => new PhoreSyslogLoggerDriver(
                    'udp://' . $parsed->host . ':' . $parsed->port . '?tag=' . urlencode($parsed->getQueryVal('tag', 'unnamed'))
                ),
                default => throw new \InvalidArgumentException("Unknown logger scheme '{$parsed->scheme}'")
            };
            $driver->setMinSeverity($severity);
            $instance->addDriver($driver);
        }
        return $instance;
    }

    private static function consoleDriver(string $host, string $uri): PhoreConsoleLoggerDriver
    {
        if (!in_array($host, ['stderr', 'stdout'], true)) {
            throw new \InvalidArgumentException("Driver spec invalid: '$uri'. Expected def://stderr|stdout");
        }
        return new PhoreConsoleLoggerDriver('php://' . $host);
    }
}
