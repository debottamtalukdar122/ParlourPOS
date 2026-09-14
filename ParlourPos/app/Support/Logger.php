<?php

declare(strict_types=1);

namespace App\Support;

final class Logger
{
    public static function error(string $message, array $context = []): void
    {
        self::write('ERROR', $message, $context);
    }

    public static function info(string $message, array $context = []): void
    {
        self::write('INFO', $message, $context);
    }

    private static function write(string $level, string $message, array $context): void
    {
        $directory = BASE_PATH . '/storage/logs';

        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $entry = sprintf(
            "[%s] %s: %s %s%s",
            gmdate('c'),
            $level,
            $message,
            $context === [] ? '' : json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            PHP_EOL
        );

        error_log($entry, 3, $directory . '/application-' . gmdate('Y-m-d') . '.log');
    }
}
