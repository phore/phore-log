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
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class PhoreLog extends AbstractLogger
{

    private static $startTime;

    const VERBOSITY_MAP = [
        LogLevel::EMERGENCY => 9,
        LogLevel::ALERT => 8,
        LogLevel::CRITICAL => 7,
        LogLevel::ERROR => 6,
        LogLevel::WARNING => 5,
        LogLevel::NOTICE => 4,
        LogLevel::INFO => 2,
        LogLevel::DEBUG => 0
    ];


    public $logger;
    public $verbosity = LogLevel::DEBUG;

    public function __construct(PhoreLogger $logger)
    {
        $this->logger = $logger;
    }

    public function getLogger() : PhoreLogger
    {
        
        return $this->logger;
    }


    public function setLogLevel(string $logLevel) : self
    {
        $map = self::VERBOSITY_MAP;
        $this->verbosity = phore_pluck($logLevel, $map, new \InvalidArgumentException("Invalid logLevel $logLevel"));
    }


    /**
     * @param int $verbosity
     * @return PhoreLog
     * @deprecated use setLogLevel() instead
     */
    public function setVerbosity(int $verbosity) : self
    {
        $this->verbosity = $verbosity;
        return $this;
    }

    public function getVerbosity() : int
    {
        return $this->verbosity;
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


    public function _log(string $severity, $message, array $context, int $btIndex=1)
    {
        if ( ! isset (self::VERBOSITY_MAP[$severity]))
            throw new \InvalidArgumentException("Invalid log-level '$severity'");

        if ($this->verbosity < self::VERBOSITY_MAP[$severity])
            return;

        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, ($btIndex + 1));
        $message = phore_escape($message, $context, function ($in) { return $in; });

        $this->logger->log(self::VERBOSITY_MAP[$severity], $backtrace[$btIndex]["file"], $backtrace[$btIndex]["line"], $message);
    }


    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function log($level, $message, array $context = array())
    {
        $this->_log($level, $message, $context);
    }


    /**
     * System is unusable.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function emergency($message, array $context = array())
    {
        $this->_log(LogLevel::EMERGENCY, $message, $context);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function alert($message, array $context = array())
    {
        $this->_log(LogLevel::ALERT, $message, $context);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function critical($message, array $context = array())
    {
        $this->_log(LogLevel::CRITICAL, $message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function error($message, array $context = array())
    {
        $this->_log(LogLevel::ERROR, $message, $context);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function warning($message, array $context = array())
    {
        $this->_log(LogLevel::WARNING, $message, $context);
    }

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function notice($message, array $context = array())
    {
        $this->_log(LogLevel::NOTICE, $message, $context);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function info($message, array $context = array())
    {
        $this->_log(LogLevel::INFO, $message, $context);
    }

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function debug($message, array $context = array())
    {
        $this->_log(LogLevel::DEBUG, $message, $context);
    }

}
