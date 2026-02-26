<?php

declare(strict_types=1);

namespace App\Core;

final class View
{
    public static function render(string $view, array $data = []): void
    {
        $viewPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR
            . str_replace('.', DIRECTORY_SEPARATOR, $view) . '.php';

        if (!is_file($viewPath)) {
            http_response_code(500);
            echo 'View nao encontrada.';
            return;
        }

        extract($data, EXTR_SKIP);
        require $viewPath;
    }
}

