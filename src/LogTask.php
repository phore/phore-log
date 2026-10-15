<?php

namespace Phore\Log;

use LogicException;

/**
 * Fortschrittsoperation mit gepufferten Unterereignissen.
 *
 * @see PhoreLogger::start()
 * @example $task = $logger->start('Bearbeite {}', ['index.md']); $task->detail('Schreibe Datei'); $task->success();
 */
final class LogTask
{
    private array $details = [];
    private bool $finished = false;

    public function __construct(
        private readonly PhoreLogger $logger,
        private readonly string $title,
        private readonly ?self $parent = null,
    ) {
        $this->render('start');
    }

    public function detail(string $message, array $context = []): void
    {
        $this->assertOpen();
        $value = self::interpolate($message, $context);
        $this->details[] = $value;
        $this->render('detail', $value);
    }

    /**
     * Unteroperation starten. Deren Status wird als Detail protokolliert.
     *
     * @see self::detail()
     * @example $child = $task->start('Lade Skills'); $child->success();
     */
    public function start(string $message, array $context = []): self
    {
        $this->assertOpen();
        return new self($this->logger, self::interpolate($message, $context), $this);
    }

    public function success(string $message = 'OK'): void
    {
        $this->complete('success', $message);
    }

    public function failure(string $message = 'ERROR'): void
    {
        $this->complete('failure', $message);
    }

    private function complete(string $status, string $message): void
    {
        $this->assertOpen();
        $this->finished = true;
        $this->render($status, $message);
        if ($this->parent !== null) {
            $this->parent->detail($this->title . ': ' . $message);
        }
    }

    private function render(string $event, string $message = ''): void
    {
        $drivers = $this->logger->getDrivers();
        foreach ($drivers as $driver) {
            if ($driver instanceof \Phore\Log\Driver\PhoreConsoleLoggerDriver) {
                $driver->taskEvent($this->title, $event, $message, $this->details);
            }
        }
        if ($drivers === []) {
            return;
        }
    }

    private function assertOpen(): void
    {
        if ($this->finished) {
            throw new LogicException('Log task already completed: ' . $this->title);
        }
    }

    private static function interpolate(string $message, array $context): string
    {
        foreach (array_values($context) as $value) {
            $message = preg_replace('/\\{\\}/', (string) $value, $message, 1) ?? $message;
        }
        return $message;
    }
}
