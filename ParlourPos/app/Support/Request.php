<?php

declare(strict_types=1);

namespace App\Support;

final class Request
{
    /** @param array<string, mixed> $query @param array<string, mixed> $body */
    public function __construct(
        private readonly string $method,
        private readonly string $path,
        private readonly array $query = [],
        private readonly array $body = []
    ) {
    }

    public static function fromGlobals(): self
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';

        return new self(
            strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET'),
            '/' . ltrim(rawurldecode($path), '/'),
            $_GET,
            $_POST
        );
    }

    public function method(): string { return $this->method; }
    public function path(): string { return $this->path; }
    public function query(string $key, mixed $default = null): mixed { return $this->query[$key] ?? $default; }
    public function input(string $key, mixed $default = null): mixed { return $this->body[$key] ?? $this->query[$key] ?? $default; }
    /** @return array<string, mixed> */
    public function all(): array { return array_merge($this->query, $this->body); }
}
