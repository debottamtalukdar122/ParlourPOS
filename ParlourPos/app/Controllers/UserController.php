<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\AuditService;
use App\Services\AuthService;
use App\Support\Flash;
use App\Support\Request;
use App\Support\Response;
use App\Support\View;
use PDO;

final class UserController extends Controller
{
    public function __construct(View $view, private readonly PDO $pdo)
    {
        parent::__construct($view);
    }

    public function index(Request $r): Response
    {
        if ($res = $this->permitted($this->pdo, 'users.manage')) {
            return $res;
        }

        $search = trim((string)$r->query('search', ''));
        $roleFilter = (int)$r->query('role_id', 0);
        $statusFilter = $r->query('status', '');

        $sql = "SELECT u.id, u.name, u.email, u.role_id, u.staff_id, u.is_active, u.last_login_at, u.created_at,
                       r.name role_name, s.name staff_name, s.staff_code, s.designation staff_designation
                FROM users u
                JOIN roles r ON r.id = u.role_id
                LEFT JOIN staff s ON s.id = u.staff_id
                WHERE 1=1";
        $params = [];

        if ($search !== '') {
            $sql .= " AND (u.name LIKE ? OR u.email LIKE ? OR s.name LIKE ? OR s.staff_code LIKE ?)";
            $term = '%' . $search . '%';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        if ($roleFilter > 0) {
            $sql .= " AND u.role_id = ?";
            $params[] = $roleFilter;
        }

        if ($statusFilter === 'active') {
            $sql .= " AND u.is_active = 1";
        } elseif ($statusFilter === 'inactive') {
            $sql .= " AND u.is_active = 0";
        }

        $sql .= " ORDER BY u.id DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $roles = $this->pdo->query("SELECT id, name FROM roles ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
        $staffList = $this->pdo->query("SELECT id, staff_code, name, designation FROM staff WHERE is_active=1 ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

        return $this->render('users.index', [
            'title' => 'User Management',
            'users' => $users,
            'roles' => $roles,
            'staffList' => $staffList,
            'search' => $search,
            'roleFilter' => $roleFilter,
            'statusFilter' => $statusFilter,
            'csrf' => Flash::csrf(),
            'flash' => Flash::get(),
        ]);
    }

    public function create(Request $r): Response
    {
        if ($res = $this->permitted($this->pdo, 'users.manage')) {
            return $res;
        }

        if (!Flash::validCsrf($r->input('_token'))) {
            Flash::set('danger', 'Your session expired. Please try again.');
            return Response::redirect('/users');
        }

        $name = trim((string)$r->input('name'));
        $email = trim((string)$r->input('email'));
        $password = (string)$r->input('password');
        $roleId = (int)$r->input('role_id');
        $staffId = (int)$r->input('staff_id') ?: null;
        $isActive = (int)$r->input('is_active', 1) === 1 ? 1 : 0;

        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Flash::set('danger', 'Please enter a valid user name and email address.');
            return Response::redirect('/users');
        }

        if (strlen($password) < 8) {
            Flash::set('danger', 'Password must be at least 8 characters long.');
            return Response::redirect('/users');
        }

        // Validate unique email
        $checkStmt = $this->pdo->prepare('SELECT id FROM users WHERE email=?');
        $checkStmt->execute([$email]);
        if ($checkStmt->fetchColumn()) {
            Flash::set('danger', 'A user account with this email address already exists.');
            return Response::redirect('/users');
        }

        // Validate role
        $roleCheck = $this->pdo->prepare('SELECT id FROM roles WHERE id=?');
        $roleCheck->execute([$roleId]);
        if (!$roleCheck->fetchColumn()) {
            Flash::set('danger', 'Selected role is invalid.');
            return Response::redirect('/users');
        }

        // Validate staff connection uniqueness
        if ($staffId !== null) {
            $staffCheck = $this->pdo->prepare('SELECT id FROM users WHERE staff_id=?');
            $staffCheck->execute([$staffId]);
            if ($staffCheck->fetchColumn()) {
                Flash::set('danger', 'This staff member is already linked to an existing user account.');
                return Response::redirect('/users');
            }
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $insert = $this->pdo->prepare('INSERT INTO users(name, email, password_hash, role_id, staff_id, is_active) VALUES(?,?,?,?,?,?)');
        $insert->execute([$name, $email, $hash, $roleId, $staffId, $isActive]);
        $newId = (int)$this->pdo->lastInsertId();

        try {
            AuditService::log($this->pdo, 'created', 'users', $newId, [
                'name' => $name,
                'email' => $email,
                'role_id' => $roleId,
                'staff_id' => $staffId,
                'is_active' => $isActive,
            ]);
        } catch (\Throwable) {}

        Flash::set('success', "User account '{$name}' created successfully.");
        return Response::redirect('/users');
    }

    public function update(Request $r): Response
    {
        if ($res = $this->permitted($this->pdo, 'users.manage')) {
            return $res;
        }

        if (!Flash::validCsrf($r->input('_token'))) {
            Flash::set('danger', 'Your session expired.');
            return Response::redirect('/users');
        }

        $id = (int)$r->input('id');
        $name = trim((string)$r->input('name'));
        $email = trim((string)$r->input('email'));
        $roleId = (int)$r->input('role_id');
        $staffId = (int)$r->input('staff_id') ?: null;
        $isActive = (int)$r->input('is_active') === 1 ? 1 : 0;
        $newPassword = (string)$r->input('password');

        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id=?');
        $stmt->execute([$id]);
        $user = $stmt->fetch();

        if (!$user) {
            Flash::set('danger', 'User not found.');
            return Response::redirect('/users');
        }

        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Flash::set('danger', 'Please enter a valid name and email address.');
            return Response::redirect('/users');
        }

        // Disallow self-deactivation
        if ($id === AuthService::userId() && $isActive === 0) {
            Flash::set('danger', 'You cannot deactivate your own active account.');
            return Response::redirect('/users');
        }

        // Validate unique email excluding current user
        $emailCheck = $this->pdo->prepare('SELECT id FROM users WHERE email=? AND id!=?');
        $emailCheck->execute([$email, $id]);
        if ($emailCheck->fetchColumn()) {
            Flash::set('danger', 'Email address is already in use by another account.');
            return Response::redirect('/users');
        }

        // Validate staff uniqueness
        if ($staffId !== null) {
            $staffCheck = $this->pdo->prepare('SELECT id FROM users WHERE staff_id=? AND id!=?');
            $staffCheck->execute([$staffId, $id]);
            if ($staffCheck->fetchColumn()) {
                Flash::set('danger', 'This staff member is already linked to another user account.');
                return Response::redirect('/users');
            }
        }

        if ($newPassword !== '') {
            if (strlen($newPassword) < 8) {
                Flash::set('danger', 'New password must be at least 8 characters long.');
                return Response::redirect('/users');
            }
            $hash = password_hash($newPassword, PASSWORD_DEFAULT);
            $this->pdo->prepare('UPDATE users SET name=?, email=?, password_hash=?, role_id=?, staff_id=?, is_active=? WHERE id=?')
                ->execute([$name, $email, $hash, $roleId, $staffId, $isActive, $id]);
        } else {
            $this->pdo->prepare('UPDATE users SET name=?, email=?, role_id=?, staff_id=?, is_active=? WHERE id=?')
                ->execute([$name, $email, $roleId, $staffId, $isActive, $id]);
        }

        try {
            AuditService::log($this->pdo, 'updated', 'users', $id, [
                'email' => $email,
                'role_id' => $roleId,
                'staff_id' => $staffId,
                'is_active' => $isActive,
                'password_reset' => $newPassword !== '',
            ]);
        } catch (\Throwable) {}

        Flash::set('success', "User account '{$name}' updated successfully.");
        return Response::redirect('/users');
    }

    public function toggleStatus(Request $r): Response
    {
        if ($res = $this->permitted($this->pdo, 'users.manage')) {
            return $res;
        }

        if (!Flash::validCsrf($r->input('_token'))) {
            Flash::set('danger', 'Your session expired.');
            return Response::redirect('/users');
        }

        $id = (int)$r->input('id');
        if ($id === AuthService::userId()) {
            Flash::set('danger', 'You cannot deactivate your own account.');
            return Response::redirect('/users');
        }

        $stmt = $this->pdo->prepare('SELECT is_active, name FROM users WHERE id=?');
        $stmt->execute([$id]);
        $user = $stmt->fetch();

        if ($user) {
            $newStatus = (int)$user['is_active'] === 1 ? 0 : 1;
            $this->pdo->prepare('UPDATE users SET is_active=? WHERE id=?')->execute([$newStatus, $id]);

            try {
                AuditService::log($this->pdo, 'status_changed', 'users', $id, ['is_active' => $newStatus]);
            } catch (\Throwable) {}

            $statusText = $newStatus === 1 ? 'activated' : 'deactivated';
            Flash::set('success', "User account '{$user['name']}' has been {$statusText}.");
        }

        return Response::redirect('/users');
    }

    public function auditLogs(Request $r): Response
    {
        if ($res = $this->permitted($this->pdo, 'audit.view')) {
            return $res;
        }

        $moduleFilter = trim((string)$r->query('module', ''));
        $sql = "SELECT al.*, u.name user_name, u.email user_email, r.name user_role
                FROM audit_logs al
                LEFT JOIN users u ON u.id = al.user_id
                LEFT JOIN roles r ON r.id = u.role_id";
        $params = [];

        if ($moduleFilter !== '') {
            $sql .= " WHERE al.module_name = ?";
            $params[] = $moduleFilter;
        }

        $sql .= " ORDER BY al.id DESC LIMIT 100";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $modules = $this->pdo->query("SELECT DISTINCT module_name FROM audit_logs ORDER BY module_name")->fetchAll(PDO::FETCH_COLUMN) ?: [];

        return $this->render('users.audit', [
            'title' => 'Audit Logs',
            'logs' => $logs,
            'modules' => $modules,
            'moduleFilter' => $moduleFilter,
            'csrf' => Flash::csrf(),
            'flash' => Flash::get(),
        ]);
    }
}
