<?php

declare(strict_types=1);

use App\Support\Router;
use App\Support\View;

return static function (Router $router, View $view, array $config): void {
    $registerWebRoutes = require BASE_PATH . '/routes/web.php';
    $registerApiRoutes = require BASE_PATH . '/routes/api.php';

    $registerWebRoutes($router, $view, $config);
    $registerApiRoutes($router, $view, $config);
};
