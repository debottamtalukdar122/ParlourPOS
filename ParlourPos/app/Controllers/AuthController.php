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

final class AuthController extends Controller
{
    public function __construct(View $view, private readonly PDO $pdo)
    {
        parent::__construct($view);
    }

    public function login(Request $r): Response
    {
        if (AuthService::userId()) {
            return Response::redirect('/dashboard');
        }

        $userCount = (int)$this->pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
        $canSetup = ($userCount === 0);

        return $this->render('auth.login', [
            'title' => 'Sign in',
            'csrf' => Flash::csrf(),
            'flash' => Flash::get(),
            'canSetup' => $canSetup,
        ]);
    }

    public function authenticate(Request $r): Response
    {
        if (!Flash::validCsrf($r->input('_token'))) {
            Flash::set('danger', 'Your session expired. Please try again.');
            return Response::redirect('/login');
        }

        $email = trim((string)$r->input('email'));
        $password = (string)$r->input('password');

        if ($email === '' || $password === '') {
            Flash::set('danger', 'Please enter your email and password.');
            return Response::redirect('/login');
        }

        $auth = new AuthService($this->pdo);
        if ($auth->attempt($email, $password)) {
            $user = AuthService::user();
            Flash::set('success', 'Welcome back, ' . ($user['name'] ?? 'User') . '.');
            return Response::redirect('/dashboard');
        }

        Flash::set('danger', 'Invalid email or password.');
        return Response::redirect('/login');
    }

    public function logout(Request $r): Response
    {
        $userId = AuthService::userId();
        if ($userId) {
            try {
                AuditService::log($this->pdo, 'logout', 'authentication', $userId, [], $userId);
            } catch (\Throwable) {}
        }

        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }

        return Response::redirect('/login');
    }

    public function setup(Request $r): Response
    {
        $exists = (int)$this->pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
        if ($exists > 0) {
            return Response::redirect('/login');
        }

        return $this->render('auth.setup', [
            'title' => 'Create administrator',
            'csrf' => Flash::csrf(),
            'flash' => Flash::get(),
        ]);
    }

    public function createSetup(Request $r): Response
    {
        $exists = (int)$this->pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
        if ($exists > 0) {
            return Response::redirect('/login');
        }

        if (!Flash::validCsrf($r->input('_token'))) {
            Flash::set('danger', 'Your session expired.');
            return Response::redirect('/setup');
        }

        $name = trim((string)$r->input('name'));
        $email = trim((string)$r->input('email'));
        $password = (string)$r->input('password');

        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
            Flash::set('danger', 'Enter a name, valid email, and a password of at least 8 characters.');
            return Response::redirect('/setup');
        }

        $role = (int)$this->pdo->query("SELECT id FROM roles WHERE name='Super Admin'")->fetchColumn();
        $this->pdo->prepare('INSERT INTO users(name,email,password_hash,role_id,is_active) VALUES(?,?,?,?,1)')
            ->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $role]);

        Flash::set('success', 'Administrator created. You can now sign in.');
        return Response::redirect('/login');
    }

    public function forgotPassword(Request $r): Response
    {
        if (AuthService::userId()) {
            return Response::redirect('/dashboard');
        }

        $demoResetLink = $_SESSION['demo_reset_link'] ?? null;
        unset($_SESSION['demo_reset_link']);

        return $this->render('auth.forgot', [
            'title' => 'Forgot Password',
            'csrf' => Flash::csrf(),
            'flash' => Flash::get(),
            'demoResetLink' => $demoResetLink,
        ]);
    }

    public function sendResetLink(Request $r): Response
    {
        if (!Flash::validCsrf($r->input('_token'))) {
            Flash::set('danger', 'Your session expired. Please try again.');
            return Response::redirect('/forgot-password');
        }

        $email = trim((string)$r->input('email'));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Flash::set('danger', 'Please enter a valid email address.');
            return Response::redirect('/forgot-password');
        }

        $stmt = $this->pdo->prepare('SELECT id, name, is_active FROM users WHERE email=?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && (int)$user['is_active'] === 1) {
            $token = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $token);
            $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1 hour

            // Invalidate any previously unused reset tokens for this user
            $this->pdo->prepare('UPDATE password_reset_tokens SET used_at=NOW() WHERE user_id=? AND used_at IS NULL')
                ->execute([(int)$user['id']]);

            // Insert new token
            $this->pdo->prepare('INSERT INTO password_reset_tokens(user_id, token_hash, expires_at) VALUES(?,?,?)')
                ->execute([(int)$user['id'], $tokenHash, $expiresAt]);

            try {
                AuditService::log($this->pdo, 'password_reset_requested', 'authentication', (int)$user['id'], ['email' => $email], (int)$user['id']);
            } catch (\Throwable) {}

            // Store demo link in session for safe local verification per requirements
            $_SESSION['demo_reset_link'] = '/reset-password?token=' . $token;
        }

        Flash::set('success', 'If an account exists with that email, password reset instructions have been generated.');
        return Response::redirect('/forgot-password');
    }

    public function resetPassword(Request $r): Response
    {
        if (AuthService::userId()) {
            return Response::redirect('/dashboard');
        }

        $token = (string)($r->query('token') ?: $r->input('token'));
        if ($token === '') {
            Flash::set('danger', 'Password reset token is missing.');
            return Response::redirect('/forgot-password');
        }

        $tokenHash = hash('sha256', $token);
        $stmt = $this->pdo->prepare(
            'SELECT prt.*, u.email, u.name 
             FROM password_reset_tokens prt 
             JOIN users u ON u.id=prt.user_id 
             WHERE prt.token_hash=? AND prt.used_at IS NULL AND prt.expires_at > NOW() AND u.is_active=1'
        );
        $stmt->execute([$tokenHash]);
        $tokenRow = $stmt->fetch();

        if (!$tokenRow) {
            Flash::set('danger', 'This password reset link is invalid, expired, or has already been used.');
            return Response::redirect('/forgot-password');
        }

        return $this->render('auth.reset', [
            'title' => 'Reset Password',
            'token' => $token,
            'email' => $tokenRow['email'],
            'csrf' => Flash::csrf(),
            'flash' => Flash::get(),
        ]);
    }

    public function updatePassword(Request $r): Response
    {
        if (!Flash::validCsrf($r->input('_token'))) {
            Flash::set('danger', 'Your session expired. Please try again.');
            return Response::redirect('/forgot-password');
        }

        $token = (string)$r->input('token');
        $password = (string)$r->input('password');
        $passwordConfirm = (string)$r->input('password_confirmation');

        $tokenHash = hash('sha256', $token);
        $stmt = $this->pdo->prepare(
            'SELECT prt.*, u.id user_id 
             FROM password_reset_tokens prt 
             JOIN users u ON u.id=prt.user_id 
             WHERE prt.token_hash=? AND prt.used_at IS NULL AND prt.expires_at > NOW() AND u.is_active=1'
        );
        $stmt->execute([$tokenHash]);
        $tokenRow = $stmt->fetch();

        if (!$tokenRow) {
            Flash::set('danger', 'This password reset link is invalid, expired, or has already been used.');
            return Response::redirect('/forgot-password');
        }

        if (strlen($password) < 8) {
            Flash::set('danger', 'Password must be at least 8 characters long.');
            return Response::redirect('/reset-password?token=' . urlencode($token));
        }

        if ($password !== $passwordConfirm) {
            Flash::set('danger', 'Password confirmation does not match.');
            return Response::redirect('/reset-password?token=' . urlencode($token));
        }

        // Update password hash and mark token used
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        $this->pdo->prepare('UPDATE users SET password_hash=? WHERE id=?')->execute([$newHash, $tokenRow['user_id']]);
        $this->pdo->prepare('UPDATE password_reset_tokens SET used_at=NOW() WHERE id=?')->execute([$tokenRow['id']]);

        try {
            AuditService::log($this->pdo, 'password_reset_completed', 'authentication', (int)$tokenRow['user_id'], [], (int)$tokenRow['user_id']);
        } catch (\Throwable) {}

        Flash::set('success', 'Your password has been successfully reset. Please sign in with your new password.');
        return Response::redirect('/login');
    }

    public function changePassword(Request $r): Response
    {
        if (!AuthService::userId()) {
            return Response::redirect('/login');
        }

        return $this->render('auth.change_password', [
            'title' => 'Change Password',
            'csrf' => Flash::csrf(),
            'flash' => Flash::get(),
        ]);
    }

    public function saveNewPassword(Request $r): Response
    {
        $userId = AuthService::userId();
        if (!$userId) {
            return Response::redirect('/login');
        }

        if (!Flash::validCsrf($r->input('_token'))) {
            Flash::set('danger', 'Your session expired. Please try again.');
            return Response::redirect('/change-password');
        }

        $current = (string)$r->input('current_password');
        $new = (string)$r->input('new_password');
        $confirm = (string)$r->input('new_password_confirmation');

        $stmt = $this->pdo->prepare('SELECT password_hash FROM users WHERE id=?');
        $stmt->execute([$userId]);
        $hash = (string)$stmt->fetchColumn();

        if (!password_verify($current, $hash)) {
            Flash::set('danger', 'Current password is incorrect.');
            return Response::redirect('/change-password');
        }

        if (strlen($new) < 8) {
            Flash::set('danger', 'New password must be at least 8 characters long.');
            return Response::redirect('/change-password');
        }

        if ($new !== $confirm) {
            Flash::set('danger', 'New password confirmation does not match.');
            return Response::redirect('/change-password');
        }

        $newHash = password_hash($new, PASSWORD_DEFAULT);
        $this->pdo->prepare('UPDATE users SET password_hash=? WHERE id=?')->execute([$newHash, $userId]);

        try {
            AuditService::log($this->pdo, 'password_change', 'authentication', $userId);
        } catch (\Throwable) {}

        Flash::set('success', 'Your password has been changed successfully.');
        return Response::redirect('/dashboard');
    }
}
