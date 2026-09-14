<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Support\Database;
use App\Support\Logger;
use App\Support\Request;
use App\Support\Response;
use Throwable;

final class HealthController
{
    /** @param array<string, string|int> $databaseConfig */
    public function __construct(private readonly array $databaseConfig)
    {
    }

    public function show(Request $request): Response
    {
        $databaseStatus = 'unavailable';
        $statusCode = 503;

        try {
            Database::connection($this->databaseConfig)->query('SELECT 1');
            $databaseStatus = 'connected';
            $statusCode = 200;
        } catch (Throwable $exception) {
            // The detailed error is intentionally not exposed by the health endpoint.
            Logger::error('Health database check failed', ['type' => $exception::class]);
        }

        return Response::json([
            'application' => 'running',
            'php' => 'running',
            'database' => $databaseStatus,
        ], $statusCode);
    }
}
