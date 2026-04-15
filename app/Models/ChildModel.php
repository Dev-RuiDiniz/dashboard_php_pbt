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
                    c.id, c.family_id, c.name, c.cpf, c.rg, c.birth_date, c.age_years, c.relationship, c.studies, c.notes, c.income,
                    f.responsible_name, f.phone, f.city, f.neighborhood
                FROM children c
                INNER JOIN families f ON f.id = c.family_id
                WHERE 1=1';
        $params = [];

        $q = trim((string) ($filters['q'] ?? ''));
        if ($q !== '') {
            $sql .= ' AND (c.name LIKE :q_child OR f.responsible_name LIKE :q_family)';
            $like = '%' . $q . '%';
            $params['q_child'] = $like;
            $params['q_family'] = $like;
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
            'SELECT id, family_id, name, cpf, rg, birth_date, age_years, relationship, studies, notes, income
             FROM children
             WHERE family_id = :family_id
             ORDER BY name ASC, id ASC'
        );
        $stmt->execute(['family_id' => $familyId]);
        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function findByEventId(int $eventId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                c.id,
                c.family_id,
                c.name,
                c.cpf,
                c.rg,
                c.birth_date,
                c.age_years,
                c.relationship,
                c.studies,
                c.notes,
                c.income,
                f.responsible_name AS family_name,
                d.ticket_number
             FROM deliveries d
             INNER JOIN families f ON f.id = d.family_id
             INNER JOIN children c ON c.family_id = d.family_id
             WHERE d.event_id = :event_id
             ORDER BY d.ticket_number ASC, c.name ASC, c.id ASC'
        );
        $stmt->execute(['event_id' => $eventId]);
        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO children (family_id, name, cpf, rg, birth_date, age_years, relationship, studies, notes, income)
             VALUES (:family_id, :name, :cpf, :rg, :birth_date, :age_years, :relationship, :studies, :notes, :income)'
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
                 cpf = :cpf,
                 rg = :rg,
                 birth_date = :birth_date,
                 age_years = :age_years,
                 relationship = :relationship,
                 studies = :studies,
                 notes = :notes,
                 income = :income
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

