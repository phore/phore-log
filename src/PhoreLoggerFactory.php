<?php


namespace Phore\Log;


use Phore\Log\Driver\PhoreEchoLoggerDriver;
use Phore\Log\Driver\PhoreSyslogLoggerDriver;

class PhoreLoggerFactory
{

    /**
     * Build a logger instance from uri string
     *
     * <example>
     * </example>
     *
     * @param string $uri
     * @return PhoreLogger
     * @throws \Exception
     */
    public static function BuildFromUri(string $uri = "") : PhoreLogger
    {
        $driverUris = explode(";", $uri);

        $instance = new PhoreLogger();
        foreach ($driverUris as $driverUri) {
            if (trim ($driverUri) === "")
                continue;
            $parsedDriverUri = phore_parse_url($driverUri);

            $severity = $parsedDriverUri->getQueryVal("severity", 7); // Default: debug - log everything
            switch ($parsedDriverUri->scheme) {
                case "def":
                    if ( ! in_array($parsedDriverUri->host, ["stderr", "stdout"]))
                        throw new \InvalidArgumentException("Driver spec invalid: '$uri'. Expeced format: def://stderr|stdout");
                    $curDriver = new PhoreEchoLoggerDriver("php://" . $parsedDriverUri->host);
                    break;

                case "file":
                    $curDriver = new PhoreEchoLoggerDriver($parsedDriverUri->path);
                    break;
                case "udp": // Backwards compat mode: Use equals syslog+udp
                case "syslog+udp":
                    $curDriver = new PhoreSyslogLoggerDriver("udp://" . $parsedDriverUri->host . ":" . $parsedDriverUri->port . "?tag=" . urlencode($parsedDriverUri->getQueryVal("tag", "unnamed")));
                    break;
                default:
                    throw new \InvalidArgumentException("Unknown logger scheme '{$parsedDriverUri->scheme}'");
            }
            $curDriver->setMinSeverity(self::severityToLogLevel($severity));
            $instance->addDriver($curDriver);
        }
        return $instance;
    }

    private static function severityToLogLevel($severity) : LogLevelEnum
    {
        if ($severity instanceof LogLevelEnum)
            return $severity;
        if (is_numeric($severity)) {
            $levels = [
                LogLevelEnum::EMERGENCY,
                LogLevelEnum::ALERT,
                LogLevelEnum::CRITICAL,
                LogLevelEnum::ERROR,
                LogLevelEnum::WARNING,
                LogLevelEnum::NOTICE,
                LogLevelEnum::INFO,
                LogLevelEnum::DEBUG
            ];
            if (isset($levels[(int)$severity]) && (string)(int)$severity === (string)$severity)
                return $levels[(int)$severity];
        } elseif (is_string($severity) && ($level = LogLevelEnum::tryFrom(strtolower($severity))) !== null) {
            return $level;
        }
        throw new \InvalidArgumentException("Invalid severity '$severity'. Expected 0-7 or a log level name.");
    }

}
