<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class UserModel
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function all(): array
    {
        $stmt = $this->pdo->query(
            'SELECT id, name, email, role, is_active, created_at, updated_at
             FROM users
             ORDER BY id DESC'
        );

        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, name, email, role, is_active, created_at, updated_at
             FROM users
             WHERE id = :id
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);

        $user = $stmt->fetch();
        return is_array($user) ? $user : null;
    }

    public function findAuthByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                id, name, email, password_hash, role, is_active,
                failed_login_attempts, locked_until,
                password_reset_token_hash, password_reset_expires_at, password_reset_requested_at
             FROM users
             WHERE email = :email
             LIMIT 1'
        );
        $stmt->execute(['email' => $email]);

        $user = $stmt->fetch();
        return is_array($user) ? $user : null;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                id, name, email, role, is_active,
                failed_login_attempts, locked_until,
                password_reset_token_hash, password_reset_expires_at, password_reset_requested_at
             FROM users
             WHERE email = :email
             LIMIT 1'
        );
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        return is_array($user) ? $user : null;
    }

    public function findByEmailExcludingId(string $email, ?int $excludeId = null): ?array
    {
        $sql = 'SELECT id, email FROM users WHERE email = :email';
        $params = ['email' => $email];

        if ($excludeId !== null) {
            $sql .= ' AND id <> :exclude_id';
            $params['exclude_id'] = $excludeId;
        }

        $sql .= ' LIMIT 1';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        $user = $stmt->fetch();
        return is_array($user) ? $user : null;
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO users (name, email, password_hash, role, is_active)
             VALUES (:name, :email, :password_hash, :role, :is_active)'
        );

        $stmt->execute([
            'name' => $data['name'],
            'email' => $data['email'],
            'password_hash' => $data['password_hash'],
            'role' => $data['role'],
            'is_active' => (int) $data['is_active'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $fields = [
            'name = :name',
            'email = :email',
            'role = :role',
            'is_active = :is_active',
        ];

        $params = [
            'id' => $id,
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'is_active' => (int) $data['is_active'],
        ];

        if (!empty($data['password_hash'])) {
            $fields[] = 'password_hash = :password_hash';
            $params['password_hash'] = $data['password_hash'];
        }

        $sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
    }

    public function setActive(int $id, bool $isActive): void
    {
        $stmt = $this->pdo->prepare('UPDATE users SET is_active = :is_active WHERE id = :id');
        $stmt->execute([
            'id' => $id,
            'is_active' => $isActive ? 1 : 0,
        ]);
    }

    public function markFailedLogin(int $id, int $maxAttempts, int $lockMinutes): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT failed_login_attempts
             FROM users
             WHERE id = :id
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        $current = (int) ($row['failed_login_attempts'] ?? 0);
        $next = $current + 1;

        $lockedUntil = null;
        if ($next >= max(1, $maxAttempts)) {
            $lockedUntil = date('Y-m-d H:i:s', strtotime('+' . max(1, $lockMinutes) . ' minutes'));
        }

        $update = $this->pdo->prepare(
            'UPDATE users
             SET failed_login_attempts = :failed_login_attempts,
                 locked_until = :locked_until
             WHERE id = :id'
        );
        $update->execute([
            'id' => $id,
            'failed_login_attempts' => $next,
            'locked_until' => $lockedUntil,
        ]);

        return [
            'attempts' => $next,
            'locked_until' => $lockedUntil,
        ];
    }

    public function clearLoginFailures(int $id): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE users
             SET failed_login_attempts = 0,
                 locked_until = NULL
             WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);
    }

    public function setPasswordResetToken(int $id, string $tokenHash, string $expiresAt): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE users
             SET password_reset_token_hash = :password_reset_token_hash,
                 password_reset_expires_at = :password_reset_expires_at,
                 password_reset_requested_at = :password_reset_requested_at
             WHERE id = :id'
        );
        $stmt->execute([
            'id' => $id,
            'password_reset_token_hash' => $tokenHash,
            'password_reset_expires_at' => $expiresAt,
            'password_reset_requested_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function findByPasswordResetToken(string $token): ?array
    {
        $tokenHash = hash('sha256', $token);
        $stmt = $this->pdo->prepare(
            'SELECT id, name, email, is_active, password_reset_expires_at
             FROM users
             WHERE password_reset_token_hash = :password_reset_token_hash
             LIMIT 1'
        );
        $stmt->execute(['password_reset_token_hash' => $tokenHash]);
        $user = $stmt->fetch();
        return is_array($user) ? $user : null;
    }

    public function updatePasswordById(int $id, string $passwordHash): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE users
             SET password_hash = :password_hash
             WHERE id = :id'
        );
        $stmt->execute([
            'id' => $id,
            'password_hash' => $passwordHash,
        ]);
    }

    public function clearPasswordResetToken(int $id): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE users
             SET password_reset_token_hash = NULL,
                 password_reset_expires_at = NULL,
                 password_reset_requested_at = NULL
             WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);
    }
}
