<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 07.08.18
 * Time: 08:23
 */

namespace Phore\Log;


use Phore\Log\Logger\PhoreLogger;
use Phore\Log\Logger\PhoreNullLogger;

class PhoreLog
{

    private static $startTime;

    const LOG_DEBUG = 9;
    const LOG_INFO = 5;
    const LOG_WARN = 3;
    const LOG_ERR = 1;


    public $logger;
    public $verbosity = self::LOG_DEBUG;

    public function __construct(PhoreLogger $logger)
    {
        $this->logger = $logger;
    }


    public function log (int $severity, string $file, int $lineNo, ...$params) : self
    {

        $this->logger->log($severity, $file, $lineNo, ...$params);
        return $this;
    }


    public function setVerbosity(int $verbosity) : self
    {
        $this->verbosity = $verbosity;
        return $this;
    }

    public function getVerbosity() : int
    {
        return $this->verbosity;
    }

    public function debug(...$params) : self
    {
        if ($this->verbosity < self::LOG_DEBUG)
            return $this;
        $bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        $this->log(self::LOG_DEBUG, $bt[0]["file"], $bt[0]["line"], ...$params);
        return $this;
    }

    public function info(...$params) : self
    {
        if ($this->verbosity < self::LOG_INFO)
            return $this;
        $bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        $this->log(self::LOG_INFO, $bt[0]["file"], $bt[0]["line"], ...$params);
        return $this;
    }

    public function warn(...$params) : self
    {
        if ($this->verbosity < self::LOG_WARN)
            return $this;
        $bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        $this->log(self::LOG_WARN, $bt[0]["file"], $bt[0]["line"], ...$params);
        return $this;
    }

    public function err(...$params) : self
    {
        if ($this->verbosity < self::LOG_ERR)
            return $this;
        $bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        $this->log(self::LOG_ERR, $bt[0]["file"], $bt[0]["line"], ...$params);
        return $this;
    }


    private static $instance = null;

    public static function Init(PhoreLogger $logger)
    {
        self::$instance = new self($logger);
    }

    public static function GetInstance()
    {
        if (self::$instance === null)
            self::$instance = new self(new PhoreNullLogger());
        return self::$instance;
    }

}
