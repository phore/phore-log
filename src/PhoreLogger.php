<?php

namespace Phore\Log;

use Phore\Log\Driver\PhoreConsoleLoggerDriver;
use Phore\Log\Driver\PhoreLoggerDriver;
use Psr\Log\AbstractLogger;

class PhoreLogger extends AbstractLogger
{
    private static ?self $instance = null;

    public function __construct(
        ?PhoreLoggerDriver $driver = null,
        private ?PhoreLoggerState $state = null,
        private string $scopeName = '',
        private array $baseContext = []
    ) {
        $this->state ??= new PhoreLoggerState();
        if ($driver !== null) {
            $this->state->drivers[] = $driver;
        }
    }

    /** @return PhoreLoggerDriver[] */
    public function getDrivers(): array
    {
        return $this->state->drivers;
    }

    public function getDriver(string $className): ?PhoreLoggerDriver
    {
        foreach ($this->state->drivers as $driver) {
            if ($driver instanceof $className) {
                return $driver;
            }
        }
        return null;
    }

    public function setDrivers(PhoreLoggerDriver $driver): self
    {
        $this->state->drivers = [$driver];
        return $this;
    }

    public function addDriver(PhoreLoggerDriver $driver): self
    {
        $this->state->drivers[] = $driver;
        return $this;
    }

    public function setLogLevel(LogLevelEnum|string|int $level): self
    {
        $this->state->config->setDefaultLevel($level);
        return $this;
    }

    public function setScopeLevel(string $scope, LogLevelEnum|string|int $level): self
    {
        $this->state->config->setScopeLevel($scope, $level);
        return $this;
    }

    public function scope(string $name): self
    {
        $name = trim($name, " .\t\n\r\0\x0B");
        if ($name === '') {
            throw new \InvalidArgumentException('Scope name must not be empty');
        }
        $scope = $this->scopeName === '' ? $name : $this->scopeName . '.' . $name;
        return new self(null, $this->state, $scope, $this->baseContext);
    }

    public function withScope(string $name): self
    {
        return $this->scope($name);
    }

    public function withContext(array $context): self
    {
        return new self(null, $this->state, $this->scopeName, array_replace($this->baseContext, $context));
    }

    public function getScope(): string
    {
        return $this->scopeName;
    }

    public function log($level, $message, array $context = []): void
    {
        $this->emit(LogLevelEnum::coerce($level), LogTypeEnum::MESSAGE, $message, $context);
    }

    public function success($message, array $context = []): void
    {
        $this->emit(LogLevelEnum::INFO, LogTypeEnum::SUCCESS, $message, $context);
    }

    public function step($message, array $context = []): void
    {
        $this->emit(LogLevelEnum::INFO, LogTypeEnum::STEP, $message, $context);
    }

    public function result($message, array $context = []): void
    {
        $this->emit(LogLevelEnum::INFO, LogTypeEnum::RESULT, $message, $context);
    }

    public function detail($message, array $context = []): void
    {
        $this->emit(LogLevelEnum::DEBUG, LogTypeEnum::DETAIL, $message, $context);
    }

    public function skip($message, array $context = []): void
    {
        $this->emit(LogLevelEnum::NOTICE, LogTypeEnum::SKIP, $message, $context);
    }

    public function failure($message, array $context = []): void
    {
        $this->emit(LogLevelEnum::ERROR, LogTypeEnum::FAILURE, $message, $context);
    }

    public function _log(LogLevelEnum $level, $message, array $context = [], int $btIndex = 1): void
    {
        $this->emit($level, LogTypeEnum::MESSAGE, $message, $context);
    }

    private function emit(LogLevelEnum $level, LogTypeEnum $type, mixed $message, array $context): void
    {
        if (!$this->state->config->shouldLog($this->scopeName, $level)) {
            return;
        }

        $source = $this->findSource();
        $record = new LogRecord(
            microtime(true),
            $level,
            $type,
            (string)$message,
            array_replace($this->baseContext, $context),
            $this->scopeName,
            $this->scopeName === '' ? 0 : substr_count($this->scopeName, '.') + 1,
            $source['file'],
            $source['line']
        );

        foreach ($this->state->drivers as $driver) {
            $driver->log($record);
        }
    }

    private function findSource(): array
    {
        foreach (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 12) as $frame) {
            $file = $frame['file'] ?? '';
            if ($file === '' || str_contains($file, '/PhoreLogger.php') || str_contains($file, '/psr/log/')) {
                continue;
            }
            return ['file' => $file, 'line' => (int)($frame['line'] ?? 0)];
        }
        return ['file' => '', 'line' => 0];
    }

    public static function Init(PhoreLoggerDriver $logger): self
    {
        self::$instance = new self($logger);
        return self::$instance;
    }

    public static function Register(PhoreLogger $logger): void
    {
        self::$instance = $logger;
    }

    public static function GetInstance(): self
    {
        return self::$instance ??= new self(new PhoreConsoleLoggerDriver());
    }
}
