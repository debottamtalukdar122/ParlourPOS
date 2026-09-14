<?php

declare(strict_types=1);

namespace App\Support;

use RuntimeException;

final class View
{
    public function __construct(private readonly string $viewsPath)
    {
    }

    /** @param array<string, mixed> $data */
    public function render(string $template, array $data = [], string $layout = 'layouts/main'): string
    {
        $content = $this->renderFile($template, $data);

        return $this->renderFile($layout, array_merge($data, ['content' => $content]));
    }

    /** @param array<string, mixed> $data */
    private function renderFile(string $template, array $viewData): string
    {
        $file = $this->viewsPath . '/' . str_replace('.', '/', $template) . '.php';

        if (!is_file($file)) {
            throw new RuntimeException("View [{$template}] was not found.");
        }

        // Do not reserve "$data" as a local variable: several views legitimately
        // receive a data payload, and EXTR_SKIP would otherwise silently hide it.
        extract($viewData, EXTR_SKIP);
        ob_start();
        require $file;

        return (string) ob_get_clean();
    }
}
