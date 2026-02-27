<?php

declare(strict_types=1);

use App\Core\Container;
use App\Core\Database;
use App\Core\Env;
use App\Core\Router;

$basePath = dirname(__DIR__);

require $basePath . '/vendor/autoload.php';

Env::load($basePath);

$isHttps = (
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (($_SERVER['SERVER_PORT'] ?? null) === '443')
);

if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_secure', $isHttps ? '1' : '0');
    ini_set('session.cookie_samesite', 'Strict');
    session_name('dashboard_php_pbt');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();
}

header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
header("Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; script-src 'self' https://cdn.jsdelivr.net; img-src 'self' data:;");

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
