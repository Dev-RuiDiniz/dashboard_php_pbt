<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class ChildModel
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function search(array $filters): array
    {
        $sql = 'SELECT
                    c.id, c.family_id, c.name, c.birth_date, c.age_years, c.relationship, c.notes,
                    f.responsible_name, f.phone, f.city, f.neighborhood
                FROM children c
                INNER JOIN families f ON f.id = c.family_id
                WHERE 1=1';
        $params = [];

        $q = trim((string) ($filters['q'] ?? ''));
        if ($q !== '') {
            $sql .= ' AND (c.name LIKE :q OR f.responsible_name LIKE :q)';
            $params['q'] = '%' . $q . '%';
        }

        $familyId = (int) ($filters['family_id'] ?? 0);
        if ($familyId > 0) {
            $sql .= ' AND c.family_id = :family_id';
            $params['family_id'] = $familyId;
        }

        $city = trim((string) ($filters['city'] ?? ''));
        if ($city !== '') {
            $sql .= ' AND f.city = :city';
            $params['city'] = $city;
        }

        $sql .= ' ORDER BY c.name ASC, c.id DESC LIMIT 300';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM children WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return is_array($row) ? $row : null;
    }

    public function findByFamilyId(int $familyId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, family_id, name, birth_date, age_years, relationship, notes
             FROM children
             WHERE family_id = :family_id
             ORDER BY name ASC, id ASC'
        );
        $stmt->execute(['family_id' => $familyId]);
        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO children (family_id, name, birth_date, age_years, relationship, notes)
             VALUES (:family_id, :name, :birth_date, :age_years, :relationship, :notes)'
        );
        $stmt->execute($data);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $data['id'] = $id;
        $stmt = $this->pdo->prepare(
            'UPDATE children
             SET family_id = :family_id,
                 name = :name,
                 birth_date = :birth_date,
                 age_years = :age_years,
                 relationship = :relationship,
                 notes = :notes
             WHERE id = :id'
        );
        $stmt->execute($data);
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM children WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}

