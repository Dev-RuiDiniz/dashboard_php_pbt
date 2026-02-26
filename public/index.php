<?php

declare(strict_types=1);

use App\Core\Container;
use App\Core\Database;
use App\Core\Env;
use App\Core\Router;

$basePath = dirname(__DIR__);

require $basePath . '/vendor/autoload.php';

Env::load($basePath);

$container = new Container();

$container->set('config', [
    'app' => require $basePath . '/config/app.php',
    'database' => require $basePath . '/config/database.php',
]);

$container->set('db', static function (Container $container) {
    $config = $container->get('config');
    return Database::connect($config['database']);
});

$router = new Router();

$routes = require $basePath . '/config/routes.php';
$routes($router, $container);

$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');

