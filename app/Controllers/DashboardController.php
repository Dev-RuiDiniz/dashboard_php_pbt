<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Container;
use App\Core\Session;
use App\Core\View;

final class DashboardController
{
    public function __construct(private readonly Container $container)
    {
    }

    public function index(): void
    {
        $user = Session::get('auth_user', []);

        View::render('dashboard.index', [
            '_layout' => 'layouts.app',
            'appName' => (string) ($this->container->get('config')['app']['name'] ?? 'Dashboard PHP PBT'),
            'pageTitle' => 'Dashboard',
            'activeMenu' => 'dashboard',
            'user' => is_array($user) ? $user : [],
            'authUser' => is_array($user) ? $user : [],
            'success' => Session::consumeFlash('success'),
        ]);
    }
}
