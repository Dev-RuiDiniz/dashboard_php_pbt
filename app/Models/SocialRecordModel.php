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

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM social_records WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return is_array($row) ? $row : null;
    }
}
