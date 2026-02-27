<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class VisitModel
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function search(array $filters): array
    {
        $sql = 'SELECT
                    v.id, v.family_id, v.person_id, v.requested_by, v.requested_at,
                    v.scheduled_date, v.completed_by, v.completed_at, v.notes, v.status, v.updated_at,
                    f.responsible_name AS family_name,
                    p.full_name AS person_full_name, p.social_name AS person_social_name,
                    ru.name AS requested_by_name,
                    cu.name AS completed_by_name
                FROM visits v
                LEFT JOIN families f ON f.id = v.family_id
                LEFT JOIN people p ON p.id = v.person_id
                LEFT JOIN users ru ON ru.id = v.requested_by
                LEFT JOIN users cu ON cu.id = v.completed_by
                WHERE 1=1';
        $params = [];

        $q = trim((string) ($filters['q'] ?? ''));
        if ($q !== '') {
            $sql .= ' AND (
                f.responsible_name LIKE :q
                OR p.full_name LIKE :q
                OR p.social_name LIKE :q
                OR v.notes LIKE :q
            )';
            $params['q'] = '%' . $q . '%';
        }

        $status = trim((string) ($filters['status'] ?? ''));
        if ($status !== '') {
            $sql .= ' AND v.status = :status';
            $params['status'] = $status;
        }

        $pendency = trim((string) ($filters['pendency'] ?? ''));
        if ($pendency === 'atrasada') {
            $sql .= ' AND v.status IN (\'pendente\', \'agendada\') AND v.scheduled_date IS NOT NULL AND v.scheduled_date < CURDATE()';
        } elseif ($pendency === 'pendente') {
            $sql .= ' AND v.status IN (\'pendente\', \'agendada\')';
        }

        $sql .= ' ORDER BY (v.status = \'concluida\') ASC, v.scheduled_date ASC, v.id DESC LIMIT 500';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM visits WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return is_array($row) ? $row : null;
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO visits (
                family_id, person_id, requested_by, requested_at, scheduled_date,
                completed_by, completed_at, notes, status
             ) VALUES (
                :family_id, :person_id, :requested_by, :requested_at, :scheduled_date,
                :completed_by, :completed_at, :notes, :status
             )'
        );
        $stmt->execute($data);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $data['id'] = $id;
        $stmt = $this->pdo->prepare(
            'UPDATE visits
             SET family_id = :family_id,
                 person_id = :person_id,
                 scheduled_date = :scheduled_date,
                 notes = :notes,
                 status = :status
             WHERE id = :id'
        );
        $stmt->execute($data);
    }

    public function conclude(int $id, int $completedBy, string $completedAt, ?string $notes): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE visits
             SET status = :status,
                 completed_by = :completed_by,
                 completed_at = :completed_at,
                 notes = :notes
             WHERE id = :id'
        );
        $stmt->execute([
            'id' => $id,
            'status' => 'concluida',
            'completed_by' => $completedBy,
            'completed_at' => $completedAt,
            'notes' => $notes,
        ]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM visits WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public function listPendingDocumentation(int $limit = 10): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, responsible_name, documentation_status, updated_at
             FROM families
             WHERE documentation_status IN (\'pendente\', \'parcial\')
             ORDER BY updated_at ASC, id ASC
             LIMIT :limit'
        );
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function countPendingDocumentation(): int
    {
        $stmt = $this->pdo->query(
            'SELECT COUNT(*) AS total
             FROM families
             WHERE documentation_status IN (\'pendente\', \'parcial\')'
        );
        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0);
    }

    public function listPendingVisits(int $limit = 10): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT v.id, v.scheduled_date, v.status, f.responsible_name AS family_name,
                    p.full_name AS person_full_name, p.social_name AS person_social_name
             FROM visits v
             LEFT JOIN families f ON f.id = v.family_id
             LEFT JOIN people p ON p.id = v.person_id
             WHERE v.status IN (\'pendente\', \'agendada\')
             ORDER BY v.scheduled_date ASC, v.id ASC
             LIMIT :limit'
        );
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function countPendingVisits(): int
    {
        $stmt = $this->pdo->query(
            'SELECT COUNT(*) AS total
             FROM visits
             WHERE status IN (\'pendente\', \'agendada\')'
        );
        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0);
    }

    public function countWithoutUpdates(int $days): int
    {
        $days = max(1, $days);
        $stmt = $this->pdo->prepare(
            'SELECT
                (
                    SELECT COUNT(*) FROM families
                    WHERE updated_at IS NOT NULL
                      AND updated_at < DATE_SUB(NOW(), INTERVAL :days_f DAY)
                ) +
                (
                    SELECT COUNT(*) FROM people
                    WHERE updated_at IS NOT NULL
                      AND updated_at < DATE_SUB(NOW(), INTERVAL :days_p DAY)
                ) AS total'
        );
        $stmt->bindValue(':days_f', $days, PDO::PARAM_INT);
        $stmt->bindValue(':days_p', $days, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0);
    }

    public function listWithoutUpdates(int $days, int $limit = 10): array
    {
        $days = max(1, $days);
        $limit = max(1, $limit);
        $sql = '(SELECT
                    \'familia\' AS entity_type,
                    f.id AS entity_id,
                    f.responsible_name AS label,
                    f.updated_at
                 FROM families f
                 WHERE f.updated_at IS NOT NULL
                   AND f.updated_at < DATE_SUB(NOW(), INTERVAL :days_f DAY))
                UNION ALL
                (SELECT
                    \'pessoa\' AS entity_type,
                    p.id AS entity_id,
                    COALESCE(NULLIF(p.full_name, \'\'), p.social_name) AS label,
                    p.updated_at
                 FROM people p
                 WHERE p.updated_at IS NOT NULL
                   AND p.updated_at < DATE_SUB(NOW(), INTERVAL :days_p DAY))
                ORDER BY updated_at ASC
                LIMIT :limit';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':days_f', $days, PDO::PARAM_INT);
        $stmt->bindValue(':days_p', $days, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }
}

