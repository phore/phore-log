<?php

namespace Phore\Log;

final class PhoreLoggerConfig
{
    private LogLevelEnum $defaultLevel = LogLevelEnum::DEBUG;
    private array $scopeLevels = [];

    public function setDefaultLevel(LogLevelEnum|string|int $level): void
    {
        $this->defaultLevel = LogLevelEnum::coerce($level);
    }

    public function setScopeLevel(string $scope, LogLevelEnum|string|int $level): void
    {
        $scope = trim($scope);
        if ($scope === '') {
            throw new \InvalidArgumentException('Scope must not be empty');
        }
        $this->scopeLevels[$scope] = LogLevelEnum::coerce($level);
    }

    public function getLevelForScope(string $scope): LogLevelEnum
    {
        $bestLevel = $this->scopeLevels['*'] ?? $this->defaultLevel;
        $bestLength = 0;
        foreach ($this->scopeLevels as $rule => $level) {
            if ($rule === '*') {
                continue;
            }
            $prefix = str_ends_with($rule, '.*') ? substr($rule, 0, -2) : $rule;
            if ($scope === $prefix || str_starts_with($scope, $prefix . '.')) {
                if (strlen($prefix) >= $bestLength) {
                    $bestLevel = $level;
                    $bestLength = strlen($prefix);
                }
            }
        }
        return $bestLevel;
    }

    public function shouldLog(string $scope, LogLevelEnum $level): bool
    {
        return $level->severity() <= $this->getLevelForScope($scope)->severity();
    }
}
