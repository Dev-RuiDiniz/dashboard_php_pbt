<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class EquipmentLoanModel
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function search(array $filters): array
    {
        $sql = 'SELECT
                    el.id, el.equipment_id, el.family_id, el.person_id,
                    el.loan_date, el.due_date, el.return_date, el.return_condition,
                    el.notes, el.created_by, el.created_at,
                    e.code AS equipment_code, e.type AS equipment_type,
                    f.responsible_name AS family_name,
                    p.full_name AS person_full_name, p.social_name AS person_social_name,
                    u.name AS created_by_name
                FROM equipment_loans el
                INNER JOIN equipment e ON e.id = el.equipment_id
                LEFT JOIN families f ON f.id = el.family_id
                LEFT JOIN people p ON p.id = el.person_id
                LEFT JOIN users u ON u.id = el.created_by
                WHERE 1=1';
        $params = [];

        $equipmentCode = trim((string) ($filters['equipment_code'] ?? ''));
        if ($equipmentCode !== '') {
            $sql .= ' AND e.code LIKE :equipment_code';
            $params['equipment_code'] = '%' . $equipmentCode . '%';
        }

        $status = trim((string) ($filters['status'] ?? ''));
        if ($status === 'aberto') {
            $sql .= ' AND el.return_date IS NULL';
        } elseif ($status === 'devolvido') {
            $sql .= ' AND el.return_date IS NOT NULL';
        } elseif ($status === 'atrasado') {
            $sql .= ' AND el.return_date IS NULL AND el.due_date < CURDATE()';
        }

        $sql .= ' ORDER BY el.return_date IS NULL DESC, el.due_date ASC, el.id DESC LIMIT 500';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM equipment_loans WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return is_array($row) ? $row : null;
    }

    public function hasOpenLoanByEquipmentId(int $equipmentId): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT 1
             FROM equipment_loans
             WHERE equipment_id = :equipment_id
               AND return_date IS NULL
             LIMIT 1'
        );
        $stmt->execute(['equipment_id' => $equipmentId]);
        return $stmt->fetchColumn() !== false;
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO equipment_loans (
                equipment_id, family_id, person_id, loan_date, due_date, return_date,
                return_condition, notes, created_by
             ) VALUES (
                :equipment_id, :family_id, :person_id, :loan_date, :due_date, :return_date,
                :return_condition, :notes, :created_by
             )'
        );
        $stmt->execute($data);
        return (int) $this->pdo->lastInsertId();
    }

    public function returnLoan(int $id, array $data): void
    {
        $data['id'] = $id;
        $stmt = $this->pdo->prepare(
            'UPDATE equipment_loans
             SET return_date = :return_date,
                 return_condition = :return_condition,
                 notes = :notes
             WHERE id = :id'
        );
        $stmt->execute($data);
    }

    public function countOverdue(): int
    {
        $stmt = $this->pdo->query(
            'SELECT COUNT(*) AS total
             FROM equipment_loans
             WHERE return_date IS NULL
               AND due_date < CURDATE()'
        );
        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0);
    }

    public function listOverdue(int $limit = 20): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                el.id, el.equipment_id, el.family_id, el.person_id, el.loan_date, el.due_date,
                e.code AS equipment_code, e.type AS equipment_type,
                f.responsible_name AS family_name,
                p.full_name AS person_full_name, p.social_name AS person_social_name
             FROM equipment_loans el
             INNER JOIN equipment e ON e.id = el.equipment_id
             LEFT JOIN families f ON f.id = el.family_id
             LEFT JOIN people p ON p.id = el.person_id
             WHERE el.return_date IS NULL
               AND el.due_date < CURDATE()
             ORDER BY el.due_date ASC, el.id ASC
             LIMIT :limit'
        );
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }
}

