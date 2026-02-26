<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    private array $routes = [
        'GET' => [],
        'POST' => [],
    ];

    public function get(string $path, callable $handler): void
    {
        $this->map('GET', $path, $handler);
    }

    public function post(string $path, callable $handler): void
    {
        $this->map('POST', $path, $handler);
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = $this->normalizePath(parse_url($uri, PHP_URL_PATH) ?: '/');
        $method = strtoupper($method);

        $handler = $this->routes[$method][$path] ?? null;

        if ($handler === null) {
            http_response_code(404);
            echo '404 - Rota nao encontrada';
            return;
        }

        $handler();
    }

    private function map(string $method, string $path, callable $handler): void
    {
        $this->routes[$method][$this->normalizePath($path)] = $handler;
    }

    private function normalizePath(string $path): string
    {
        $normalized = '/' . trim($path, '/');
        return $normalized === '//' ? '/' : $normalized;
    }
}

