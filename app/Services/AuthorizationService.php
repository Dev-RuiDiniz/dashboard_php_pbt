<?php

declare(strict_types=1);

namespace App\Services;

final class AuthorizationService
{
    private const ROLE_PERMISSIONS = [
        'admin' => ['*'],
        'voluntario' => ['families.view', 'families.manage', 'children.view', 'children.manage', 'people.view', 'people.manage', 'deliveries.view', 'deliveries.manage', 'equipment.view', 'equipment.manage'],
        'pastoral' => ['families.view', 'children.view', 'people.view', 'people.manage', 'deliveries.view', 'equipment.view'],
        'viewer' => ['families.view', 'children.view', 'people.view', 'deliveries.view', 'equipment.view'],
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
