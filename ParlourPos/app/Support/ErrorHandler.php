<?php

declare(strict_types=1);

namespace App\Support;

use ErrorException;
use Throwable;

final class ErrorHandler
{
    public static function register(bool $debug): void
    {
        ini_set('display_errors', $debug ? '1' : '0');
        error_reporting(E_ALL);

        set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
            if (!(error_reporting() & $severity)) {
                return false;
            }

            throw new ErrorException($message, 0, $severity, $file, $line);
        });

        set_exception_handler(static function (Throwable $exception) use ($debug): void {
            Logger::error('Unhandled application exception', [
                'type' => $exception::class,
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);

            http_response_code(500);
            header('Content-Type: text/html; charset=UTF-8');

            if ($debug) {
                echo '<h1>Application error</h1><pre>' . htmlspecialchars((string) $exception, ENT_QUOTES, 'UTF-8') . '</pre>';
                return;
            }

            echo '<h1>Something went wrong.</h1><p>Please try again later.</p>';
        });
    }
}
