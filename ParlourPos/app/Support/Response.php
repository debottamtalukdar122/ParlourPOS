<?php

declare(strict_types=1);

namespace App\Support;

final class Response
{
    /** @param array<string, string> $headers */
    public function __construct(
        private readonly string $body,
        private readonly int $status = 200,
        private readonly array $headers = ['Content-Type' => 'text/html; charset=UTF-8']
    ) {
    }

    public static function html(string $html, int $status = 200): self { return new self($html, $status); }
    public static function text(string $text, int $status = 200): self { return new self($text, $status, ['Content-Type' => 'text/plain; charset=UTF-8']); }
    public static function json(array $data, int $status = 200): self { return new self(json_encode($data, JSON_THROW_ON_ERROR), $status, ['Content-Type' => 'application/json; charset=UTF-8']); }
    public static function redirect(string $location): self { return new self('', 302, ['Location' => $location]); }

    public function send(): void
    {
        http_response_code($this->status);
        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value);
        }
        echo $this->body;
    }
}
