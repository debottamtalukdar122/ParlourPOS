<?php
declare(strict_types=1);
namespace App\Support;
final class Flash {
    public static function set(string $type, string $message): void { $_SESSION['flash'] = compact('type', 'message'); }
    /** @return array{type:string,message:string}|null */
    public static function get(): ?array { $flash = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $flash; }
    public static function csrf(): string { return $_SESSION['csrf'] ??= bin2hex(random_bytes(32)); }
    public static function validCsrf(mixed $value): bool { return is_string($value) && isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $value); }
}
