<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Core\Response;
use App\Core\Session;

final class AuthMiddleware
{
    public function handle(callable $next): void
    {
        if (!Session::has('auth_user')) {
            Session::flash('error', 'Faça login para acessar o dashboard.');
            Response::redirect('/login');
        }

        $next();
    }
}

