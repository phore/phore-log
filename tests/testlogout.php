<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 28.11.19
 * Time: 17:13
 */

namespace Test;

use Phore\Log\Logger\PhoreEchoLoggerDriver;
use Phore\Log\Logger\PhoreSyslogLoggerDriver;
use Phore\Log\PhoreLogger;
use Psr\Log\LogLevel;

require __DIR__ . "/../vendor/autoload.php";



$log = PhoreLogger::Init(new PhoreEchoLoggerDriver())->addDriver($sl = new PhoreSyslogLoggerDriver("udp://google.de:4000?tag=demo", LogLevel::NOTICE));


$log->setLogLevel(LogLevel::NOTICE);


$log->warning("warning");
echo "\nSL: $sl->lastMsg";


echo "\n NOTICE:";

$log->notice("notice");
echo "\nSL: $sl->lastMsg";
