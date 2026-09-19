<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

final class View
{
    private static string $viewsPath;

    public static function setPath(string $path): void
    {
        self::$viewsPath = rtrim($path, DIRECTORY_SEPARATOR);
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function render(string $view, array $data = [], ?string $layout = 'layouts/main'): string
    {
        if (!isset(self::$viewsPath)) {
            self::$viewsPath = dirname(__DIR__, 2) . '/views';
        }

        $content = self::renderFile($view, $data);

        if ($layout === null) {
            return $content;
        }

        return self::renderFile($layout, array_merge($data, ['content' => $content]));
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function renderFile(string $view, array $data): string
    {
        $file = self::$viewsPath . '/' . str_replace('.', '/', $view) . '.php';

        if (!is_readable($file)) {
            throw new RuntimeException("Vue introuvable : {$view}");
        }

        extract($data, EXTR_SKIP);

        ob_start();
        include $file;

        return (string) ob_get_clean();
    }
}
