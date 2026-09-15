<?php

namespace Phore\Log\Driver;

use Phore\Core\Exception\InvalidDataException;
use Phore\Log\Format\PhoreLogFormat;
use Phore\Log\Format\PhoreSyslogLogFormat;
use Phore\Log\LogLevelEnum;
use Phore\Log\LogRecord;

class PhoreSyslogLoggerDriver implements PhoreLoggerDriver
{
    protected mixed $sock;
    protected ?string $syslogHostAddr;
    protected int $syslogPort;
    protected LogLevelEnum $minLevel;
    protected PhoreLogFormat $logFormat;

    public function __construct(string $syslogConn, LogLevelEnum $minLogLevel = LogLevelEnum::DEBUG)
    {
        $this->sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        $url = phore_parse_url($syslogConn, 'udp://localhost:4200?facility=2');
        $this->syslogHostAddr = gethostbyname($url->host);
        try {
            phore_assert($this->syslogHostAddr)->filter(FILTER_VALIDATE_IP);
        } catch (InvalidDataException) {
            $this->syslogHostAddr = null;
        }
        $this->minLevel = $minLogLevel;
        $this->syslogPort = (int)$url->port;
        $this->logFormat = new PhoreSyslogLogFormat([
            'facility' => (int)$url->getQueryVal('facility', 2),
            'tag' => $url->getQueryVal('tag', 'unnamed')
        ]);
    }

    public function getSyslogHostAddr(): ?string
    {
        return $this->syslogHostAddr;
    }

    public function log(LogRecord $record): void
    {
        if ($this->syslogHostAddr === null || $record->level->severity() > $this->minLevel->severity()) return;
        $message = $this->logFormat->format($record);
        socket_sendto($this->sock, $message, strlen($message), 0, $this->syslogHostAddr, $this->syslogPort);
    }

    public function setMinSeverity(LogLevelEnum $logLevel): void
    {
        $this->minLevel = $logLevel;
    }

    public function setFormatter(PhoreLogFormat $logFormat): void
    {
        $this->logFormat = $logFormat;
    }

    public function __destruct()
    {
        if ($this->sock !== false && $this->sock !== null) socket_close($this->sock);
    }
}
