<?php

declare(strict_types=1);

namespace App\Core;

final class View
{
    public static function render(string $view, array $data = []): void
    {
        $layout = null;
        if (array_key_exists('_layout', $data)) {
            $layout = is_string($data['_layout']) ? $data['_layout'] : null;
            unset($data['_layout']);
        }

        $viewPath = self::resolvePath($view);

        if (!is_file($viewPath)) {
            http_response_code(500);
            echo 'View nao encontrada.';
            return;
        }

        if ($layout === null) {
            extract($data, EXTR_SKIP);
            require $viewPath;
            return;
        }

        $layoutPath = self::resolvePath($layout);
        if (!is_file($layoutPath)) {
            http_response_code(500);
            echo 'Layout nao encontrado.';
            return;
        }

        extract($data, EXTR_SKIP);
        ob_start();
        require $viewPath;
        $content = (string) ob_get_clean();
        require $layoutPath;
    }

    private static function resolvePath(string $view): string
    {
        return dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR
            . str_replace('.', DIRECTORY_SEPARATOR, $view) . '.php';
    }
}
