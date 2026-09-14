<?php

declare(strict_types=1);

namespace App\Support;

use Closure;

final class Router
{
    /** @var array<string, array<string, Closure>> */
    private array $routes = [];

    public function get(string $path, Closure $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, Closure $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    public function add(string $method, string $path, Closure $handler): void
    {
        $this->routes[$method][$this->normalize($path)] = $handler;
    }

    public function dispatch(Request $request): Response
    {
        $path = $this->normalize($request->path());
        $handler = $this->routes[$request->method()][$path] ?? null;

        if ($handler === null) {
            foreach ($this->routes[$request->method()] ?? [] as $pattern => $candidate) {
                $regex = '#^' . preg_replace('#\\{[a-zA-Z_][a-zA-Z0-9_]*\\}#', '([^/]+)', $pattern) . '$#';
                if (preg_match($regex, $path, $matches) === 1) {
                    array_shift($matches);
                    return $candidate($request, ...array_map('rawurldecode', $matches));
                }
            }

            return Response::html('<h1>404 - Page not found</h1>', 404);
        }

        return $handler($request);
    }

    private function normalize(string $path): string
    {
        $path = '/' . trim($path, '/');

        return $path === '/' ? $path : rtrim($path, '/');
    }
}
