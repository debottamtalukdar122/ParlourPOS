<?php

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . '/app/Support/Autoloader.php';

use App\Support\Autoloader;
use App\Support\Env;
use App\Support\ErrorHandler;

Autoloader::register(BASE_PATH);
Env::load(BASE_PATH . '/.env');

$config = [
    'app' => require BASE_PATH . '/config/app.php',
    'database' => require BASE_PATH . '/config/database.php',
];

date_default_timezone_set($config['app']['timezone']);
ErrorHandler::register($config['app']['debug']);

return $config;
