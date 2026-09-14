<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Support\Response;
use App\Support\View;
use App\Services\AuthService;
use PDO;

abstract class Controller
{
    public function __construct(protected readonly View $view)
    {
    }

    /** @param array<string, mixed> $data */
    protected function render(string $template, array $data = [], int $status = 200): Response
    {
        return Response::html($this->view->render($template, $data), $status);
    }
    protected function permitted(PDO $pdo, string $permission): ?Response
    {
        if (!AuthService::userId()) {
            return Response::redirect('/login');
        }

        if (!AuthService::can($pdo, $permission)) {
            $html = '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>403 - Access Denied</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"><link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet"></head><body class="bg-light d-flex align-items-center justify-content-center min-vh-100 p-3"><div class="card shadow-sm border-0 text-center p-4 p-md-5" style="max-width: 480px;"><div class="text-danger display-1 mb-3"><i class="bi bi-shield-lock"></i></div><h1 class="h3 fw-bold mb-2">403 - Access Denied</h1><p class="text-muted mb-4">You do not have the required permission (<code>' . htmlspecialchars($permission) . '</code>) to access this module.</p><div class="d-flex justify-content-center gap-2"><a href="/dashboard" class="btn btn-primary"><i class="bi bi-house me-1"></i> Return to Dashboard</a><a href="/logout" class="btn btn-outline-secondary">Sign out</a></div></div></body></html>';
            return Response::html($html, 403);
        }

        return null;
    }
}
