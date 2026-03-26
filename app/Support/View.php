<?php

declare(strict_types=1);

final class View
{
    public static function render(string $view, array $data = [], string $layout = 'app'): void
    {
        $viewFile = __DIR__ . '/../../views/' . trim($view, '/') . '.php';

        if (! file_exists($viewFile)) {
            throw new RuntimeException(sprintf('View not found: %s', $view));
        }

        extract($data, EXTR_SKIP);
        $contentView = $viewFile;
        $layoutFile = __DIR__ . '/../../views/layouts/' . trim($layout, '/') . '.php';

        if (! file_exists($layoutFile)) {
            throw new RuntimeException(sprintf('Layout not found: %s', $layout));
        }

        require $layoutFile;
    }
}
