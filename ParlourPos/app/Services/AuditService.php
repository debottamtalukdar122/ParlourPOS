<?php

declare(strict_types=1);

namespace App\Services;

use PDO;

final class AuditService
{
    public static function log(PDO $pdo, string $action, string $module, ?int $recordId = null, array $context = [], ?int $actorId = null): void
    {
        $userId = $actorId ?? AuthService::userId();
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $json = $context ? json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : null;

        try {
            $pdo->prepare('INSERT INTO audit_logs(user_id, action, module_name, record_id, context_json, ip_address) VALUES(?,?,?,?,?,?)')
                ->execute([$userId, $action, $module, $recordId, $json, $ip]);
        } catch (\Throwable) {
            try {
                $pdo->prepare('INSERT INTO audit_logs(user_id, action, module_name, record_id, context_json) VALUES(?,?,?,?,?)')
                    ->execute([$userId, $action, $module, $recordId, $json]);
            } catch (\Throwable) {}
        }
    }
}
