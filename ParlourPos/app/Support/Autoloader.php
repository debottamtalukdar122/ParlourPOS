<?php

declare(strict_types=1);

namespace App\Support;

final class Autoloader
{
    public static function register(string $basePath): void
    {
        $vendorAutoload = $basePath . '/vendor/autoload.php';

        if (is_file($vendorAutoload)) {
            require_once $vendorAutoload;
            return;
        }

        spl_autoload_register(static function (string $class) use ($basePath): void {
            $prefix = 'App\\';

            if (!str_starts_with($class, $prefix)) {
                return;
            }

            $relativeClass = substr($class, strlen($prefix));
            $file = $basePath . '/app/' . str_replace('\\', '/', $relativeClass) . '.php';

            if (is_file($file)) {
                require_once $file;
            }
        });
    }
}
