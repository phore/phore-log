<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 07.08.18
 * Time: 08:23
 */

namespace Phore\Log;


use Phore\Log\Logger\PhoreEchoLoggerDriver;
use Phore\Log\Logger\PhoreLoggerDriver;
use Phore\Log\Logger\PhoreNullLoggerDriver;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class PhoreLogger extends AbstractLogger
{

    private static $startTime;

    const SEVERITY_MAP = [
        LogLevel::EMERGENCY => 0,
        LogLevel::ALERT => 1,
        LogLevel::CRITICAL => 2,
        LogLevel::ERROR => 3,
        LogLevel::WARNING => 4,
        LogLevel::NOTICE => 5,
        LogLevel::INFO => 6,
        LogLevel::DEBUG => 7
    ];

    const LOG_LEVEL_MAP = [
        0 => LogLevel::EMERGENCY,
        1 => LogLevel::ALERT,
        2 => LogLevel::CRITICAL,
        3 => LogLevel::ERROR,
        4 => LogLevel::WARNING,
        5 => LogLevel::NOTICE,
        6 => LogLevel::INFO,
        7 => LogLevel::DEBUG
    ];

    /**
     * @var PhoreLoggerDriver
     */
    private $drivers = [];

    private $minSeverity = 7;

    public function __construct(PhoreLoggerDriver $driver)
    {
        $this->drivers = [$driver];
        $this->setLogLevel(LogLevel::DEBUG); //Default: Highest log level
    }

    /**
     * @return PhoreLoggerDriver[]
     */
    public function getDrivers() : array
    {
        return $this->drivers;
    }

    public function getDriver(string $className) : ?PhoreLoggerDriver
    {
        foreach ($this->drivers as $curDriver) {
            if ($curDriver instanceof $className)
                return $curDriver;
        }
        return null;
    }

    public function setDrivers(PhoreLoggerDriver $drivers) : self
    {
        $this->drivers = [$drivers];
        return $this;
    }

    public function addDriver(PhoreLoggerDriver $driver) : self
    {
        $this->drivers[] = $driver;
        return $this;
    }


    public function setLogLevel(string $logLevel) : self
    {
        $map = self::SEVERITY_MAP;
        $this->minSeverity = phore_pluck($logLevel, $map, new \InvalidArgumentException("Invalid logLevel $logLevel"));
        return $this;
    }


    private static $instance = null;

    public static function Init(PhoreLoggerDriver $logger) : self
    {
        self::$instance = new self($logger);
        return self::$instance;
    }

    public static function GetInstance()
    {
        if (self::$instance === null)
            self::$instance = new self(new PhoreEchoLoggerDriver()); // Default output to stdout
        return self::$instance;
    }


    public function _log(string $logLevel, $message, array $context, int $btIndex=1)
    {
        if ( ! isset (self::SEVERITY_MAP[$logLevel]))
            throw new \InvalidArgumentException("Invalid log-level '$logLevel'");

        if ($this->minSeverity < self::SEVERITY_MAP[$logLevel])
            return;

        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, ($btIndex + 1));
        $message = phore_escape($message, $context, function ($in) { return $in; }, true);

        foreach ($this->drivers as $driver)
            $driver->log(self::SEVERITY_MAP[$logLevel], $backtrace[$btIndex]["file"], $backtrace[$btIndex]["line"], $message);
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
