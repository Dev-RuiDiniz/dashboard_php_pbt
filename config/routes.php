<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Core\Container;
use App\Core\Router;
use App\Middlewares\AuthMiddleware;

return static function (Router $router, Container $container): void {
    $router->get('/', static function () use ($container): void {
        if (\App\Core\Session::has('auth_user')) {
            \App\Core\Response::redirect('/dashboard');
        }

        \App\Core\Response::redirect('/login');
    });

    $router->get('/login', static function () use ($container): void {
        (new AuthController($container))->showLogin();
    });

    $router->post('/login', static function () use ($container): void {
        (new AuthController($container))->login();
    });

    $router->post('/logout', static function () use ($container): void {
        (new AuthController($container))->logout();
    });

    $router->get('/logout', static function () use ($container): void {
        (new AuthController($container))->logout();
    });

    $router->get('/dashboard', static function () use ($container): void {
        (new AuthMiddleware())->handle(static function () use ($container): void {
            (new DashboardController($container))->index();
        });
    });
};
