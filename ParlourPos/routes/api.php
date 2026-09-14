<?php

declare(strict_types=1);

use App\Support\Router;
use App\Support\View;

// Phase 0 has no business or AJAX endpoints. This file reserves the API route boundary.
return static function (Router $router, View $view, array $config): void {
};
