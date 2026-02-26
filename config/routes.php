<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\FamilyController;
use App\Controllers\UserController;
use App\Core\Container;
use App\Core\Router;
use App\Middlewares\AuthMiddleware;
use App\Middlewares\PermissionMiddleware;

return static function (Router $router, Container $container): void {
    $authOnly = static function (callable $action): void {
        (new AuthMiddleware())->handle($action);
    };

    $adminOnly = static function (callable $action): void {
        (new AuthMiddleware())->handle(static function () use ($action): void {
            (new PermissionMiddleware())->handle('users.manage', $action);
        });
    };

    $familyView = static function (callable $action): void {
        (new AuthMiddleware())->handle(static function () use ($action): void {
            (new PermissionMiddleware())->handle('families.view', $action);
        });
    };

    $familyManage = static function (callable $action): void {
        (new AuthMiddleware())->handle(static function () use ($action): void {
            (new PermissionMiddleware())->handle('families.manage', $action);
        });
    };

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

    $router->get('/dashboard', static function () use ($container, $authOnly): void {
        $authOnly(static function () use ($container): void {
            (new DashboardController($container))->index();
        });
    });

    $router->get('/families', static function () use ($container, $familyView): void {
        $familyView(static function () use ($container): void {
            (new FamilyController($container))->index();
        });
    });

    $router->get('/families/create', static function () use ($container, $familyManage): void {
        $familyManage(static function () use ($container): void {
            (new FamilyController($container))->create();
        });
    });

    $router->post('/families', static function () use ($container, $familyManage): void {
        $familyManage(static function () use ($container): void {
            (new FamilyController($container))->store();
        });
    });

    $router->get('/families/edit', static function () use ($container, $familyManage): void {
        $familyManage(static function () use ($container): void {
            (new FamilyController($container))->edit();
        });
    });

    $router->post('/families/update', static function () use ($container, $familyManage): void {
        $familyManage(static function () use ($container): void {
            (new FamilyController($container))->update();
        });
    });

    $router->get('/users', static function () use ($container, $adminOnly): void {
        $adminOnly(static function () use ($container): void {
            (new UserController($container))->index();
        });
    });

    $router->get('/users/create', static function () use ($container, $adminOnly): void {
        $adminOnly(static function () use ($container): void {
            (new UserController($container))->create();
        });
    });

    $router->post('/users', static function () use ($container, $adminOnly): void {
        $adminOnly(static function () use ($container): void {
            (new UserController($container))->store();
        });
    });

    $router->get('/users/edit', static function () use ($container, $adminOnly): void {
        $adminOnly(static function () use ($container): void {
            (new UserController($container))->edit();
        });
    });

    $router->post('/users/update', static function () use ($container, $adminOnly): void {
        $adminOnly(static function () use ($container): void {
            (new UserController($container))->update();
        });
    });

    $router->post('/users/toggle', static function () use ($container, $adminOnly): void {
        $adminOnly(static function () use ($container): void {
            (new UserController($container))->toggleActive();
        });
    });
};
