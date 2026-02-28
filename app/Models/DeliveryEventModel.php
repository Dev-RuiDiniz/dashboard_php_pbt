<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class DeliveryEventModel
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function search(array $filters): array
    {
        $sql = 'SELECT
                    de.id, de.name, de.event_date, de.block_multiple_same_month, de.max_baskets,
                    de.status, de.created_by, de.created_at, de.updated_at,
                    u.name AS created_by_name
                FROM delivery_events de
                INNER JOIN users u ON u.id = de.created_by
                WHERE 1=1';
        $params = [];

        $q = trim((string) ($filters['q'] ?? ''));
        if ($q !== '') {
            $sql .= ' AND de.name LIKE :q';
            $params['q'] = '%' . $q . '%';
        }

        $status = trim((string) ($filters['status'] ?? ''));
        if ($status !== '') {
            $sql .= ' AND de.status = :status';
            $params['status'] = $status;
        }

        $month = trim((string) ($filters['month'] ?? ''));
        if ($month !== '' && preg_match('/^\d{4}-\d{2}$/', $month) === 1) {
            $sql .= ' AND DATE_FORMAT(de.event_date, "%Y-%m") = :month';
            $params['month'] = $month;
        }

        $sql .= ' ORDER BY de.event_date DESC, de.id DESC LIMIT 200';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM delivery_events WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return is_array($row) ? $row : null;
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO delivery_events (name, event_date, block_multiple_same_month, max_baskets, status, created_by)
             VALUES (:name, :event_date, :block_multiple_same_month, :max_baskets, :status, :created_by)'
        );
        $stmt->execute($data);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $data['id'] = $id;
        $stmt = $this->pdo->prepare(
            'UPDATE delivery_events
             SET name = :name,
                 event_date = :event_date,
                 block_multiple_same_month = :block_multiple_same_month,
                 max_baskets = :max_baskets,
                 status = :status
             WHERE id = :id'
        );
        $stmt->execute($data);
    }

    public function updateStatus(int $id, string $status): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE delivery_events
             SET status = :status
             WHERE id = :id'
        );
        $stmt->execute([
            'id' => $id,
            'status' => $status,
        ]);
    }
}

