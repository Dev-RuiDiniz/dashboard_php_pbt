<?php

declare(strict_types=1);

namespace App\Services;

final class AuthorizationService
{
    private const ROLE_PERMISSIONS = [
        'admin' => ['*'],
        'voluntario' => ['families.view', 'families.manage'],
        'pastoral' => ['families.view'],
        'viewer' => ['families.view'],
    ];

    public static function can(?string $role, string $permission): bool
    {
        if ($role === null || $role === '') {
            return false;
        }

        $permissions = self::ROLE_PERMISSIONS[$role] ?? [];

        return in_array('*', $permissions, true) || in_array($permission, $permissions, true);
    }
}
