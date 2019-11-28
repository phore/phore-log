<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 20.11.19
 * Time: 19:29
 */

namespace Phore\Log\Logger;


use Phore\Core\Exception\InvalidDataException;
use Phore\Log\PhoreLogger;
use Psr\Log\LogLevel;

class PhoreSyslogLoggerDriver implements PhoreLoggerDriver
{


    protected $sock;

    protected $syslogHostAddr;
    protected $syslogPort;


    protected $facility;
    protected $tag;

    protected $syslogType;

    protected $minLogLevel;

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
    public function __construct(string $syslogConn, string $minLogLevel = LogLevel::DEBUG)
    {
        $this->sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);

        $url = phore_parse_url($syslogConn, "udp://localhost:4200?facility=2");
        $this->syslogHostAddr = gethostbyname($url->host);

        try {
            phore_assert($this->syslogHostAddr)->filter(FILTER_VALIDATE_IP);
        } catch (InvalidDataException $e) {
            $this->syslogHostAddr = null;
        }

        $this->minLogLevel = $minLogLevel;

        $this->facility = (int)$url->getQueryVal("facility", 2);
        $this->syslogPort = (int)$url->port;
        $this->tag = $url->getQueryVal("tag", "unnamed");
        $this->syslogType = strtoupper($url->getQueryVal("type", "RFC3164"));
    }


    public function getSyslogHostAddr()
    {
        return $this->syslogHostAddr;
    }


    public function log(int $severity, string $file, int $lineNo, ...$params)
    {
        if ($this->syslogHostAddr === null)
            return;

        if ($severity > PhoreLogger::SEVERITY_MAP[$this->minLogLevel])
            return;


        if ($this->syslogType === "RFC3164") {

            $pri = (int)(($this->facility * 8) + $severity);

            $date = date('M d H:i:s');
            $host = gethostname();
            $message = "[" . PhoreLogger::LOG_LEVEL_MAP[$severity] . "] " . implode(" ", $params);
            $syslog_message = "<$pri>{$date} {$host} {$this->tag}: $message";

            $syslog_message = substr($syslog_message,0 ,  1495);
            $this->lastMsg = $syslog_message;

            socket_sendto($this->sock, $syslog_message, strlen($syslog_message), 0, $this->syslogHostAddr, $this->syslogPort);
        }
    }

    public function __destruct()
    {
        socket_close($this->sock);
    }
}
