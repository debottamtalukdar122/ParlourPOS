<?php

declare(strict_types=1);

namespace App\Services;

use PDO;

final class AuthService
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function attempt(string $email, string $password): bool
    {
        $stmt = $this->pdo->prepare('SELECT u.*, r.name role_name FROM users u JOIN roles r ON r.id=u.role_id WHERE u.email=?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // 1. Check user existence and account activation
        if (!$user || (int)$user['is_active'] !== 1) {
            try {
                AuditService::log($this->pdo, 'failed_login', 'authentication', $user ? (int)$user['id'] : null, ['email' => $email, 'reason' => $user ? 'inactive' : 'not_found']);
            } catch (\Throwable) {}
            return false;
        }

        // 2. Verify password hash
        if (!password_verify($password, (string)$user['password_hash'])) {
            try {
                AuditService::log($this->pdo, 'failed_login', 'authentication', (int)$user['id'], ['email' => $email, 'reason' => 'invalid_password']);
            } catch (\Throwable) {}
            return false;
        }

        // 3. Establish secure session
        session_regenerate_id(true);

        // 4. Fetch user permissions
        $permCodes = self::fetchPermissionsForRole($this->pdo, (int)$user['role_id'], (string)$user['role_name']);

        $_SESSION['user'] = [
            'id' => (int)$user['id'],
            'name' => (string)$user['name'],
            'email' => (string)$user['email'],
            'role' => (string)$user['role_name'],
            'role_name' => (string)$user['role_name'],
            'role_id' => (int)$user['role_id'],
            'staff_id' => !empty($user['staff_id']) ? (int)$user['staff_id'] : null,
            'permissions' => $permCodes,
        ];

        // 5. Update last login timestamp
        $this->pdo->prepare('UPDATE users SET last_login_at=NOW() WHERE id=?')->execute([$user['id']]);

        // 6. Record successful login audit log
        try {
            AuditService::log($this->pdo, 'login', 'authentication', (int)$user['id'], ['ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1']);
        } catch (\Throwable) {}

        return true;
    }

    public static function userId(): ?int
    {
        return isset($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : null;
    }

    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function validateSession(PDO $pdo): ?array
    {
        if (empty($_SESSION['user']['id'])) {
            return null;
        }

        $userId = (int)$_SESSION['user']['id'];
        $stmt = $pdo->prepare('SELECT u.id, u.name, u.email, u.role_id, u.staff_id, u.is_active, r.name role_name FROM users u JOIN roles r ON r.id = u.role_id WHERE u.id = ?');
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || (int)$user['is_active'] !== 1) {
            $_SESSION = [];
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"] ?: '/',
                    $params["domain"],
                    $params["secure"],
                    $params["httponly"]
                );
            }
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_destroy();
            }
            return null;
        }

        $_SESSION['user']['name'] = (string)$user['name'];
        $_SESSION['user']['email'] = (string)$user['email'];
        $_SESSION['user']['role'] = (string)$user['role_name'];
        $_SESSION['user']['role_name'] = (string)$user['role_name'];
        $_SESSION['user']['role_id'] = (int)$user['role_id'];
        $_SESSION['user']['staff_id'] = !empty($user['staff_id']) ? (int)$user['staff_id'] : null;

        return $_SESSION['user'];
    }

    public static function role(): ?string
    {
        return $_SESSION['user']['role'] ?? $_SESSION['user']['role_name'] ?? null;
    }

    public static function staffId(): ?int
    {
        return isset($_SESSION['user']['staff_id']) ? (int)$_SESSION['user']['staff_id'] : null;
    }

    public static function userStaffId(): ?int
    {
        return self::staffId();
    }

    public static function isAdmin(): bool
    {
        return (self::role() ?? '') === 'Super Admin';
    }

    public static function isStaff(): bool
    {
        return (self::role() ?? '') === 'Staff';
    }

    public static function can(PDO $pdo, string $permission): bool
    {
        if (self::isAdmin()) {
            return true;
        }

        $userId = self::userId();
        if (!$userId) {
            return false;
        }

        // Check cached session permissions if present
        if (isset($_SESSION['user']['permissions']) && is_array($_SESSION['user']['permissions'])) {
            return in_array($permission, $_SESSION['user']['permissions'], true);
        }

        // Fallback to database query
        $stmt = $pdo->prepare('SELECT 1 FROM users u JOIN role_permissions rp ON rp.role_id=u.role_id JOIN permissions p ON p.id=rp.permission_id WHERE u.id=? AND p.code=?');
        $stmt->execute([$userId, $permission]);
        return (bool)$stmt->fetchColumn();
    }

    public static function permissions(PDO $pdo): array
    {
        if (isset($_SESSION['user']['permissions']) && is_array($_SESSION['user']['permissions'])) {
            return $_SESSION['user']['permissions'];
        }

        $userId = self::userId();
        if (!$userId) return [];

        $roleId = (int)($_SESSION['user']['role_id'] ?? 0);
        $roleName = (string)($_SESSION['user']['role'] ?? '');

        return self::fetchPermissionsForRole($pdo, $roleId, $roleName);
    }

    public static function userPermissions(PDO $pdo, int $userId): array
    {
        $stmt = $pdo->prepare('SELECT u.role_id, r.name role_name FROM users u JOIN roles r ON r.id=u.role_id WHERE u.id=?');
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        if (!$user) return [];
        return self::fetchPermissionsForRole($pdo, (int)$user['role_id'], (string)$user['role_name']);
    }

    private static function fetchPermissionsForRole(PDO $pdo, int $roleId, string $roleName): array
    {
        if ($roleName === 'Super Admin') {
            return $pdo->query('SELECT code FROM permissions')->fetchAll(PDO::FETCH_COLUMN) ?: [];
        }

        $stmt = $pdo->prepare('SELECT p.code FROM role_permissions rp JOIN permissions p ON p.id=rp.permission_id WHERE rp.role_id=?');
        $stmt->execute([$roleId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
    }
}
