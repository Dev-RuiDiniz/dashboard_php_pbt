<?php

declare(strict_types=1);

use App\Controllers\HomeController;
use App\Core\Container;
use App\Core\Router;

return static function (Router $router, Container $container): void {
    $router->get('/', static function () use ($container): void {
        (new HomeController($container))->index();
    });
};

