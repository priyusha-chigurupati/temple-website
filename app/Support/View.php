<?php

declare(strict_types=1);

final class View
{
    public static function render(string $view, array $data = []): void
    {
        $viewFile = __DIR__ . '/../../views/' . trim($view, '/') . '.php';

        if (! file_exists($viewFile)) {
            throw new RuntimeException(sprintf('View not found: %s', $view));
        }

        extract($data, EXTR_SKIP);
        $contentView = $viewFile;

        require __DIR__ . '/../../views/layouts/app.php';
    }
}
