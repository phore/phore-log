<?php

namespace Phore\Log;

final class LogPlaceholderFileStore
{
    private static bool $initialized = false;
    private static int $counter = 0;

    public static function write(string $content, ?string $directory = null): string
    {
        $directory ??= sys_get_temp_dir();
        self::initialize($directory);

        self::$counter++;
        $path = rtrim($directory, DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR
            . sprintf('phore-log-%06d.txt', self::$counter);

        if (file_put_contents($path, $content) === false) {
            throw new \RuntimeException("Cannot write log placeholder file '$path'");
        }

        $absolutePath = realpath($path) ?: $path;
        return 'file://' . $absolutePath;
    }

    private static function initialize(string $directory): void
    {
        if (self::$initialized) {
            return;
        }

        self::$initialized = true;
        $pattern = rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'phore-log-*.txt';
        foreach (glob($pattern) ?: [] as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }
    }
}
