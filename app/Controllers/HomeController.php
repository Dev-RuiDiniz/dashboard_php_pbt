<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Container;
use App\Core\View;
use PDO;
use Throwable;

final class HomeController
{
    public function __construct(private readonly Container $container)
    {
    }

    public function index(): void
    {
        $dbStatus = 'nao configurado';

        try {
            /** @var PDO $pdo */
            $pdo = $this->container->get('db');
            $pdo->query('SELECT 1');
            $dbStatus = 'conectado';
        } catch (Throwable $exception) {
            $dbStatus = 'erro de conexao';
        }

        View::render('home', [
            'appName' => (string) ($this->container->get('config')['app']['name'] ?? 'Dashboard PHP PBT'),
            'dbStatus' => $dbStatus,
        ]);
    }
}

