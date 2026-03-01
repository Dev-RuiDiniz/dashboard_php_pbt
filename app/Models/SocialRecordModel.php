<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class SocialRecordModel
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function findByPersonId(int $personId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                sr.id, sr.person_id, sr.family_id, sr.chronic_diseases, sr.continuous_medication,
                sr.substance_use, sr.disability, sr.immediate_needs,
                sr.spiritual_wants_prayer, sr.spiritual_accepts_visit, sr.church_name, sr.spiritual_decision,
                sr.notes, sr.consent_text_version, sr.consent_name, sr.consent_at, sr.created_at,
                sr.created_by, u.name AS created_by_name, f.responsible_name AS family_name
             FROM social_records sr
             INNER JOIN users u ON u.id = sr.created_by
             LEFT JOIN families f ON f.id = sr.family_id
             WHERE sr.person_id = :person_id
             ORDER BY sr.created_at DESC, sr.id DESC'
        );
        $stmt->execute(['person_id' => $personId]);
        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO social_records (
                person_id, family_id, chronic_diseases, continuous_medication, substance_use, disability,
                immediate_needs, spiritual_wants_prayer, spiritual_accepts_visit, church_name, spiritual_decision,
                notes, consent_text_version, consent_name, consent_at, created_by
            ) VALUES (
                :person_id, :family_id, :chronic_diseases, :continuous_medication, :substance_use, :disability,
                :immediate_needs, :spiritual_wants_prayer, :spiritual_accepts_visit, :church_name, :spiritual_decision,
                :notes, :consent_text_version, :consent_name, :consent_at, :created_by
            )'
        );
        $stmt->execute($data);
        return (int) $this->pdo->lastInsertId();
    }

    public function search(array $filters): array
    {
        [$whereSql, $params] = $this->buildFilterWhere($filters);

        $sql = 'SELECT
                    sr.id,
                    sr.person_id,
                    sr.family_id,
                    sr.immediate_needs,
                    sr.spiritual_wants_prayer,
                    sr.spiritual_accepts_visit,
                    sr.consent_name,
                    sr.consent_at,
                    sr.created_at,
                    p.full_name,
                    p.social_name,
                    f.responsible_name AS family_name,
                    u.name AS created_by_name
                FROM social_records sr
                INNER JOIN people p ON p.id = sr.person_id
                INNER JOIN users u ON u.id = sr.created_by
                LEFT JOIN families f ON f.id = sr.family_id' . $whereSql . '
                ORDER BY sr.created_at DESC, sr.id DESC
                LIMIT 400';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function summary(array $filters): array
    {
        [$whereSql, $params] = $this->buildFilterWhere($filters);

        $sql = 'SELECT
                    COUNT(*) AS total_records,
                    COALESCE(SUM(CASE WHEN sr.family_id IS NOT NULL THEN 1 ELSE 0 END), 0) AS linked_family,
                    COALESCE(SUM(CASE WHEN sr.spiritual_wants_prayer = 1 THEN 1 ELSE 0 END), 0) AS wants_prayer,
                    COALESCE(SUM(CASE WHEN sr.spiritual_accepts_visit = 1 THEN 1 ELSE 0 END), 0) AS accepts_visit
                FROM social_records sr
                INNER JOIN people p ON p.id = sr.person_id
                LEFT JOIN families f ON f.id = sr.family_id' . $whereSql;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return is_array($row) ? $row : [];
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM social_records WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return is_array($row) ? $row : null;
    }

    private function buildFilterWhere(array $filters): array
    {
        $where = ' WHERE 1=1';
        $params = [];

        $q = trim((string) ($filters['q'] ?? ''));
        if ($q !== '') {
            $where .= ' AND (
                p.full_name LIKE :q
                OR p.social_name LIKE :q
                OR sr.consent_name LIKE :q
                OR sr.immediate_needs LIKE :q
            )';
            $params['q'] = '%' . $q . '%';
        }

        $dateFrom = trim((string) ($filters['date_from'] ?? ''));
        if ($dateFrom !== '') {
            $where .= ' AND DATE(sr.created_at) >= :date_from';
            $params['date_from'] = $dateFrom;
        }

        $dateTo = trim((string) ($filters['date_to'] ?? ''));
        if ($dateTo !== '') {
            $where .= ' AND DATE(sr.created_at) <= :date_to';
            $params['date_to'] = $dateTo;
        }

        $hasFamily = trim((string) ($filters['has_family'] ?? ''));
        if ($hasFamily === '1') {
            $where .= ' AND sr.family_id IS NOT NULL';
        } elseif ($hasFamily === '0') {
            $where .= ' AND sr.family_id IS NULL';
        }

        $spiritual = trim((string) ($filters['spiritual'] ?? ''));
        if ($spiritual === 'prayer') {
            $where .= ' AND sr.spiritual_wants_prayer = 1';
        } elseif ($spiritual === 'visit') {
            $where .= ' AND sr.spiritual_accepts_visit = 1';
        }

        return [$where, $params];
    }
}
