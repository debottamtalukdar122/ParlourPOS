<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Support\Request;
use App\Support\Response;
use Closure;

final class CsrfMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, Closure $next): Response
    {
        // Phase 1 will verify CSRF tokens on state-changing requests here.
        return $next($request);
    }
}
