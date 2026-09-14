<?php

declare(strict_types=1);

$config = require dirname(__DIR__) . '/bootstrap/app.php';

use App\Support\Database;
use App\Support\Logger;
use App\Support\Migrator;

try {
    $migrator = new Migrator(Database::connection($config['database']), BASE_PATH . '/database/migrations');
    $count = $migrator->migrate();
    echo $count === 0 ? "No pending migrations." . PHP_EOL : "Applied {$count} migration(s)." . PHP_EOL;
} catch (Throwable $exception) {
    Logger::error('Migration command failed', ['type' => $exception::class, 'message' => $exception->getMessage()]);
    fwrite(STDERR, 'Migration failed. Check your .env database settings and storage/logs for details.' . PHP_EOL);
    exit(1);
}
