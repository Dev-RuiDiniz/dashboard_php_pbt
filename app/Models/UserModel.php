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
            'SELECT id, name, email, password_hash, role, is_active
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
}

