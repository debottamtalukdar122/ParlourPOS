<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Support\Request;
use App\Support\Response;
use Closure;

final class PermissionMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, Closure $next): Response
    {
        // Phase 1 will resolve and enforce named role permissions here.
        return $next($request);
    }
}
