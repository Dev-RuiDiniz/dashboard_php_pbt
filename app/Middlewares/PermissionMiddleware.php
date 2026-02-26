<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Core\Session;
use App\Services\AuthorizationService;

final class PermissionMiddleware
{
    public function handle(string $permission, callable $next): void
    {
        $user = Session::get('auth_user');
        $role = is_array($user) ? (string) ($user['role'] ?? '') : '';

        if (!AuthorizationService::can($role, $permission)) {
            http_response_code(403);
            echo '403 - Acesso negado';
            return;
        }

        $next();
    }
}
