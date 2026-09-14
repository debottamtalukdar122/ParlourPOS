<?php

declare(strict_types=1);

$config = require dirname(__DIR__) . '/bootstrap/app.php';

session_name('parlour_pos');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();

use App\Support\Request;
use App\Support\Router;
use App\Support\View;

$router = new Router();
$view = new View(BASE_PATH . '/app/Views');
$registerRoutes = require BASE_PATH . '/config/routes.php';
$registerRoutes($router, $view, $config);

$router->dispatch(Request::fromGlobals())->send();
