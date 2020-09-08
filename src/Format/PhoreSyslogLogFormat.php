<?php


namespace Phore\Log\Format;


class PhoreSyslogLogFormat implements PhoreLogFormat
{

    private $options;
    public function __construct($options=["facility" => 2, "tag" => "unnamed"])
    {
        $this->options = $options;
    }

    public function format(int $severity, string $file, int $lineNo, ...$params): string
    {
        $pri = (int)(($this->options["facility"] * 8) + $severity);

        $date = date('M d H:i:s');
        $host = gethostname();
        $message = implode(" ", $params);
        $syslog_message = "<$pri>{$date} {$host} {$this->options["tag"]}: $message";

        $syslog_message = substr($syslog_message,0 ,  1390); // 1390 works for google MTU
        return $syslog_message;
    }
}
