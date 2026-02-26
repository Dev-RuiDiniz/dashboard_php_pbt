<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Session;
use App\Models\UserModel;
use PDO;

final class AuthService
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function attempt(string $email, string $password): bool
    {
        $user = (new UserModel($this->pdo))->findAuthByEmail($email);
        if ($user === false) {
            return false;
        }
        if ($user === null) {
            return false;
        }

        if ((int) ($user['is_active'] ?? 0) !== 1) {
            return false;
        }

        $hash = (string) ($user['password_hash'] ?? '');
        if (!password_verify($password, $hash)) {
            return false;
        }

        session_regenerate_id(true);

        Session::set('auth_user', [
            'id' => (int) $user['id'],
            'name' => (string) $user['name'],
            'email' => (string) $user['email'],
            'role' => (string) $user['role'],
        ]);

        return true;
    }

    public function check(): bool
    {
        return Session::has('auth_user');
    }

    public function user(): ?array
    {
        $user = Session::get('auth_user');
        return is_array($user) ? $user : null;
    }

    public function logout(): void
    {
        Session::remove('auth_user');
        session_regenerate_id(true);
    }
}
