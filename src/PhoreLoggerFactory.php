<?php


namespace Phore\Log;


use Phore\Log\Logger\PhoreEchoLoggerDriver;
use Phore\Log\Logger\PhoreSyslogLoggerDriver;

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
            $curDriver->setSeverity($severity);
            $instance->addDriver($curDriver);
        }
        return $instance;
    }

}
