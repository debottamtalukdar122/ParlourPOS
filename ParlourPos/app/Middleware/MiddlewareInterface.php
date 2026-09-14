<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Support\Request;
use App\Support\Response;
use Closure;

interface MiddlewareInterface
{
    /** @param Closure(Request): Response $next */
    public function handle(Request $request, Closure $next): Response;
}
