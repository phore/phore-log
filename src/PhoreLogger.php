<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 07.08.18
 * Time: 08:23
 */

namespace Phore\Log;



use Phore\Log\Driver\PhoreEchoLoggerDriver;
use Phore\Log\Driver\PhoreLoggerDriver;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class PhoreLogger extends AbstractLogger
{

    private static $startTime;


    /**
     * @var PhoreLoggerDriver
     */
    private $drivers = [];

    private $minSeverity = 7;

    public function __construct(PhoreLoggerDriver $driver = null)
    {
        if ($driver !== null)
            $this->drivers = [$driver];
        $this->setLogLevel(LogLevelEnum::DEBUG); //Default: Highest log level
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


    /**
     * Set the global limit of what to log. Default is DEBUG - log everything
     *
     * You should not use this method but instead configure appropriate logLevels
     * for the individual drivers
     *
     * @param string $logLevel
     * @return $this
     * @throws \Exception
     */
    public function setLogLevel(LogLevelEnum $logLevel) : self
    {
        $this->minSeverity = phore_loglevel_to_int($logLevel);
        return $this;
    }


    private static $instance = null;

    /**
     * @deprecated
     * @param PhoreLoggerDriver $logger
     * @return static
     */
    public static function Init(PhoreLoggerDriver $logger) : self
    {
        self::$instance = new self($logger);
        return self::$instance;
    }

    /**
     * Register the global logging instance available with phore_log();
     *
     * @param PhoreLogger $logger
     */
    public static function Register(PhoreLogger $logger)
    {
        self::$instance = $logger;
    }

    public static function GetInstance()
    {
        if (self::$instance === null)
            self::$instance = new self(new PhoreEchoLoggerDriver()); // Default output to stdout
        return self::$instance;
    }


    public function _log(LogLevelEnum $logLevel, $message, array $context, int $btIndex=1)
    {


        if ($this->minSeverity < phore_loglevel_to_int($logLevel))
            return;

        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, ($btIndex + 1));
        $message = phore_escape($message, $context, function ($in) { return $in; }, true);

        foreach ($this->drivers as $driver)
            $driver->log($logLevel, $backtrace[$btIndex]["file"], $backtrace[$btIndex]["line"], $message);
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
        $this->_log(LogLevelEnum::EMERGENCY, $message, $context);
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
        $this->_log(LogLevelEnum::ALERT, $message, $context);
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
        $this->_log(LogLevelEnum::CRITICAL, $message, $context);
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
        $this->_log(LogLevelEnum::ERROR, $message, $context);
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
        $this->_log(LogLevelEnum::WARNING, $message, $context);
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
        $this->_log(LogLevelEnum::NOTICE, $message, $context);
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
        $this->_log(LogLevelEnum::INFO, $message, $context);
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
        $this->_log(LogLevelEnum::DEBUG, $message, $context);
    }


    public function success($message, array $context = array())
    {
        $this->_log(LogLevelEnum::SUCCESS, $message, $context);
    }
}
