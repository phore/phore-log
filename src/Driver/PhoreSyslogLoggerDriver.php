<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 20.11.19
 * Time: 19:29
 */

namespace Phore\Log\Driver;


use Phore\Core\Exception\InvalidDataException;
use Phore\Log\Format\PhoreLogFormat;
use Phore\Log\Format\PhoreSyslogLogFormat;
use Phore\Log\LogLevelEnum;
use Phore\Log\PhoreLogger;
use Psr\Log\LogLevel;

class PhoreSyslogLoggerDriver implements PhoreLoggerDriver
{


    protected $sock;
    protected $syslogHostAddr;
    protected $syslogPort;

    protected $minSeverity;

    /**
     * @var PhoreLogFormat
     */
    protected $logFormat;

    public $lastMsg;

    /**
     * PhoreSyslogLoggerDriver constructor.
     *
     * <example>
     *
     * $driver = new PhoreSyslogLoggerDriver("udp://localhost:4100?tag=someTag&facility=2&type=rfc3164", "some_service");
     *
     * </example>
     *
     *
     * @param string $syslogConn
     * @param string $tag
     */
    public function __construct(string $syslogConn, LogLevelEnum $minLogLevel = LogLevelEnum::DEBUG)
    {
        $this->sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);

        $url = phore_parse_url($syslogConn, "udp://localhost:4200?facility=2");
        $this->syslogHostAddr = gethostbyname($url->host);

        try {
            phore_assert($this->syslogHostAddr)->filter(FILTER_VALIDATE_IP);
        } catch (InvalidDataException $e) {
            $this->syslogHostAddr = null;
        }

        $this->minSeverity = phore_loglevel_to_int($minLogLevel);

        $this->syslogPort = (int)$url->port;
        $facility = (int)$url->getQueryVal("facility", 2);
        $tag = $url->getQueryVal("tag", "unnamed");
        $syslogType = strtoupper($url->getQueryVal("type", "RFC3164"));

        $this->logFormat = new PhoreSyslogLogFormat([
            "facility" => $facility,
            "tag" => $tag
        ]);
    }


    public function getSyslogHostAddr()
    {
        return $this->syslogHostAddr;
    }


    public function log (LogLevelEnum $logLevel, string $file, int $lineNo, $message, $context = [])
    {
        if ($this->syslogHostAddr === null)
            return;

        if ($logLevel > $this->minSeverity)
            return;

        $syslog_message = $this->logFormat->format($logLevel, $file, $lineNo, ...$params);
        socket_sendto($this->sock, $syslog_message, strlen($syslog_message), 0, $this->syslogHostAddr, $this->syslogPort);
    }

    public function __destruct()
    {
        socket_close($this->sock);
    }

    public function setMinSeverity(LogLevelEnum $logLevel)
    {
        $this->minSeverity = phore_loglevel_to_int($logLevel);
    }

    public function setFormatter(PhoreLogFormat $logFormat)
    {
        $this->logFormat = $logFormat;
    }
}
