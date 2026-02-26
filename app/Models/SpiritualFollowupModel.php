<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class SpiritualFollowupModel
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function findByPersonId(int $personId, array $filters = []): array
    {
        $sql = 'SELECT
                    sf.id, sf.person_id, sf.followup_date, sf.action, sf.notes, sf.created_by,
                    u.name AS created_by_name
                FROM spiritual_followups sf
                INNER JOIN users u ON u.id = sf.created_by
                WHERE sf.person_id = :person_id';
        $params = ['person_id' => $personId];

        $action = trim((string) ($filters['action'] ?? ''));
        if ($action !== '') {
            $sql .= ' AND sf.action = :action';
            $params['action'] = $action;
        }

        $sql .= ' ORDER BY sf.followup_date DESC, sf.id DESC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM spiritual_followups WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return is_array($row) ? $row : null;
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO spiritual_followups (person_id, followup_date, action, notes, created_by)
             VALUES (:person_id, :followup_date, :action, :notes, :created_by)'
        );
        $stmt->execute($data);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $data['id'] = $id;
        $stmt = $this->pdo->prepare(
            'UPDATE spiritual_followups
             SET followup_date = :followup_date,
                 action = :action,
                 notes = :notes
             WHERE id = :id AND person_id = :person_id'
        );
        $stmt->execute($data);
    }

    public function delete(int $id, int $personId): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM spiritual_followups WHERE id = :id AND person_id = :person_id');
        $stmt->execute(['id' => $id, 'person_id' => $personId]);
    }
}

