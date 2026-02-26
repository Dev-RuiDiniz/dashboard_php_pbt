<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class ReferralModel
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function findByPersonId(int $personId, array $filters = []): array
    {
        $sql = 'SELECT
                    r.id, r.social_record_id, r.referral_type, r.referral_date, r.responsible_user_id,
                    r.status, r.notes, r.created_at, r.updated_at,
                    sr.person_id, u.name AS responsible_user_name
                FROM referrals r
                INNER JOIN social_records sr ON sr.id = r.social_record_id
                INNER JOIN users u ON u.id = r.responsible_user_id
                WHERE sr.person_id = :person_id';
        $params = ['person_id' => $personId];

        $type = trim((string) ($filters['referral_type'] ?? ''));
        if ($type !== '') {
            $sql .= ' AND r.referral_type = :referral_type';
            $params['referral_type'] = $type;
        }

        $status = trim((string) ($filters['status'] ?? ''));
        if ($status !== '') {
            $sql .= ' AND r.status = :status';
            $params['status'] = $status;
        }

        $sql .= ' ORDER BY r.referral_date DESC, r.id DESC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT r.*, sr.person_id
             FROM referrals r
             INNER JOIN social_records sr ON sr.id = r.social_record_id
             WHERE r.id = :id
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return is_array($row) ? $row : null;
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO referrals (social_record_id, referral_type, referral_date, responsible_user_id, status, notes)
             VALUES (:social_record_id, :referral_type, :referral_date, :responsible_user_id, :status, :notes)'
        );
        $stmt->execute($data);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $data['id'] = $id;
        $stmt = $this->pdo->prepare(
            'UPDATE referrals
             SET social_record_id = :social_record_id,
                 referral_type = :referral_type,
                 referral_date = :referral_date,
                 responsible_user_id = :responsible_user_id,
                 status = :status,
                 notes = :notes
             WHERE id = :id'
        );
        $stmt->execute($data);
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM referrals WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}

