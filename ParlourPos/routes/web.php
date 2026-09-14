<?php

declare(strict_types=1);

use App\Controllers\HealthController;
use App\Controllers\AuthController;
use App\Controllers\AppController;
use App\Controllers\AppointmentController;
use App\Controllers\PosController;
use App\Controllers\OperationsController;
use App\Controllers\UserController;
use App\Controllers\StaffController;
use App\Support\Database;
use App\Support\Router;
use App\Support\View;

return static function (Router $router, View $view, array $config): void {
    $pdo = Database::connection($config['database']);
    \App\Services\AuthService::validateSession($pdo);
    $auth = new AuthController($view, $pdo);
    $app = new AppController($view, $pdo);
    $appointments = new AppointmentController($view, $pdo);
    $pos = new PosController($view, $pdo);
    $operations = new OperationsController($view, $pdo);
    $users = new UserController($view, $pdo);
    $staff = new StaffController($view, $pdo);
    $healthController = new HealthController($config['database']);

    $router->get('/', static fn () => \App\Support\Response::redirect(\App\Services\AuthService::userId() ? '/dashboard' : '/login'));
    $router->get('/health', $healthController->show(...));
    $router->get('/setup', $auth->setup(...)); $router->post('/setup', $auth->createSetup(...));
    $router->get('/login', $auth->login(...)); $router->post('/login', $auth->authenticate(...));
    $router->get('/logout', $auth->logout(...)); $router->post('/logout', $auth->logout(...));

    // Password reset & change
    $router->get('/forgot-password', $auth->forgotPassword(...));
    $router->post('/forgot-password', $auth->sendResetLink(...));
    $router->get('/reset-password', $auth->resetPassword(...));
    $router->post('/reset-password', $auth->updatePassword(...));
    $router->get('/change-password', $auth->changePassword(...));
    $router->post('/change-password', $auth->saveNewPassword(...));

    // Dashboard
    $router->get('/dashboard', $app->dashboard(...));

    // Staff management with profile photos & documents
    $router->get('/staff', $staff->index(...));
    $router->post('/staff', $staff->create(...));
    $router->post('/staff/update', $staff->update(...));
    $router->post('/staff/toggle-status', $staff->toggleStatus(...));
    $router->post('/staff/documents/upload', $staff->uploadDocument(...));
    $router->get('/staff/documents/{id}/download', $staff->downloadDocument(...));
    $router->post('/staff/documents/delete', $staff->deleteDocument(...));

    // Business entity modules (Staff now has dedicated controller)
    foreach (['customers','services','products','suppliers','expenses'] as $module) {
        $router->get('/'.$module, static fn ($r) => $app->index($r, $module));
        $router->post('/'.$module, static fn ($r) => $app->create($r, $module));
    }

    // User management & audit logs
    $router->get('/users', $users->index(...));
    $router->post('/users/create', $users->create(...));
    $router->post('/users/update', $users->update(...));
    $router->post('/users/toggle-status', $users->toggleStatus(...));
    $router->get('/audit-logs', $users->auditLogs(...));

    // Appointments
    $router->get('/appointments', $appointments->index(...));
    $router->post('/appointments', $appointments->create(...));
    $router->post('/appointments/status', $appointments->updateStatus(...));

    // POS & billing
    $router->get('/pos', $pos->index(...));
    $router->post('/pos/finalize', $pos->finalize(...));
    $router->get('/invoices/{id}', $pos->invoice(...));

    // Purchases & reports (Inventory standalone module removed; stock ledger internally preserved)
    $router->get('/purchases', $operations->purchases(...));
    $router->post('/purchases', $operations->createPurchase(...));
    $router->get('/reports', $operations->reports(...));
};
