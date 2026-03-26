<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class FamilyModel
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function search(array $filters): array
    {
        $sql = 'SELECT
                    id, responsible_name, cpf_responsible, phone,
                    neighborhood, city, state, family_income_total, family_income_average,
                    children_count, documentation_status, needs_visit, is_active, created_at, updated_at
                FROM families
                WHERE 1=1';
        $params = [];

        $sql .= $this->buildFilterSql($filters, $params);

        $sql .= ' ORDER BY responsible_name ASC, id DESC LIMIT 200';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    public function count(array $filters = []): int
    {
        $sql = 'SELECT COUNT(*) AS total FROM families WHERE 1=1';
        $params = [];
        $sql .= $this->buildFilterSql($filters, $params);

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM families WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return is_array($row) ? $row : null;
    }

    public function getPhones(int $familyId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, family_id, number, label, sort_order, is_primary, created_at, updated_at
             FROM family_phones
             WHERE family_id = :family_id
             ORDER BY is_primary DESC, sort_order ASC, id ASC'
        );
        $stmt->execute(['family_id' => $familyId]);
        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function replacePhones(int $familyId, array $phones): void
    {
        $delete = $this->pdo->prepare('DELETE FROM family_phones WHERE family_id = :family_id');
        $delete->execute(['family_id' => $familyId]);

        if ($phones === []) {
            return;
        }

        $insert = $this->pdo->prepare(
            'INSERT INTO family_phones (family_id, number, label, sort_order, is_primary)
             VALUES (:family_id, :number, :label, :sort_order, :is_primary)'
        );

        foreach ($phones as $phone) {
            $insert->execute([
                'family_id' => $familyId,
                'number' => $phone['number'],
                'label' => ($phone['label'] ?? '') !== '' ? $phone['label'] : null,
                'sort_order' => (int) ($phone['sort_order'] ?? 0),
                'is_primary' => (int) ($phone['is_primary'] ?? 0),
            ]);
        }
    }

    public function findByCpfExcludingId(string $cpfFormatted, ?int $excludeId = null): ?array
    {
        $sql = 'SELECT id, cpf_responsible, responsible_name FROM families WHERE cpf_responsible = :cpf';
        $params = ['cpf' => $cpfFormatted];

        if ($excludeId !== null) {
            $sql .= ' AND id <> :id';
            $params['id'] = $excludeId;
        }

        $sql .= ' LIMIT 1';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return is_array($row) ? $row : null;
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO families (
                responsible_name, cpf_responsible, rg_responsible, birth_date, phone,
                responsible_works, responsible_income,
                marital_status, education_level, professional_status, profession_detail,
                cep, address, address_number, address_complement, neighborhood, city, state,
                location_reference, housing_type, documentation_status,
                documentation_notes, needs_visit, general_notes, is_active,
                chronic_disease, has_physical_disability, physical_disability_details,
                uses_continuous_medication, continuous_medication_details, social_benefit
            ) VALUES (
                :responsible_name, :cpf_responsible, :rg_responsible, :birth_date, :phone,
                :responsible_works, :responsible_income,
                :marital_status, :education_level, :professional_status, :profession_detail,
                :cep, :address, :address_number, :address_complement, :neighborhood, :city, :state,
                :location_reference, :housing_type, :documentation_status,
                :documentation_notes, :needs_visit, :general_notes, :is_active,
                :chronic_disease, :has_physical_disability, :physical_disability_details,
                :uses_continuous_medication, :continuous_medication_details, :social_benefit
            )'
        );
        $stmt->execute($data);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $data['id'] = $id;

        $stmt = $this->pdo->prepare(
            'UPDATE families SET
                responsible_name = :responsible_name,
                cpf_responsible = :cpf_responsible,
                rg_responsible = :rg_responsible,
                birth_date = :birth_date,
                phone = :phone,
                responsible_works = :responsible_works,
                responsible_income = :responsible_income,
                marital_status = :marital_status,
                education_level = :education_level,
                professional_status = :professional_status,
                profession_detail = :profession_detail,
                cep = :cep,
                address = :address,
                address_number = :address_number,
                address_complement = :address_complement,
                neighborhood = :neighborhood,
                city = :city,
                state = :state,
                location_reference = :location_reference,
                housing_type = :housing_type,
                documentation_status = :documentation_status,
                documentation_notes = :documentation_notes,
                needs_visit = :needs_visit,
                general_notes = :general_notes,
                is_active = :is_active,
                chronic_disease = :chronic_disease,
                has_physical_disability = :has_physical_disability,
                physical_disability_details = :physical_disability_details,
                uses_continuous_medication = :uses_continuous_medication,
                continuous_medication_details = :continuous_medication_details,
                social_benefit = :social_benefit
            WHERE id = :id'
        );

        $stmt->execute($data);
    }

    public function updateResponsible(int $id, array $data): void
    {
        $data['id'] = $id;

        $stmt = $this->pdo->prepare(
            'UPDATE families
             SET responsible_name = :responsible_name,
                 cpf_responsible = :cpf_responsible,
                 rg_responsible = :rg_responsible,
                 birth_date = :birth_date,
                 phone = :phone,
                 responsible_works = :responsible_works,
                 responsible_income = :responsible_income
             WHERE id = :id'
        );

        $stmt->execute($data);
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM families WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public function getMembersByFamilyId(int $familyId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, family_id, name, relationship, cpf, rg, birth_date, studies, works, income, created_at, updated_at
             FROM family_members
             WHERE family_id = :family_id
             ORDER BY name ASC, id ASC'
        );
        $stmt->execute(['family_id' => $familyId]);
        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function findMemberById(int $memberId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, family_id, name, relationship, cpf, rg, birth_date, studies, works, income
             FROM family_members
             WHERE id = :id
             LIMIT 1'
        );
        $stmt->execute(['id' => $memberId]);
        $row = $stmt->fetch();
        return is_array($row) ? $row : null;
    }

    public function createMember(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO family_members (family_id, name, relationship, cpf, rg, birth_date, studies, works, income)
             VALUES (:family_id, :name, :relationship, :cpf, :rg, :birth_date, :studies, :works, :income)'
        );
        $stmt->execute($data);
        return (int) $this->pdo->lastInsertId();
    }

    public function updateMember(int $memberId, array $data): void
    {
        $data['id'] = $memberId;
        $stmt = $this->pdo->prepare(
            'UPDATE family_members
             SET name = :name,
                 relationship = :relationship,
                 cpf = :cpf,
                 rg = :rg,
                 birth_date = :birth_date,
                 studies = :studies,
                 works = :works,
                 income = :income
             WHERE id = :id AND family_id = :family_id'
        );
        $stmt->execute($data);
    }

    public function findCpfConflict(string $cpfFormatted, array $exclude = []): ?array
    {
        $familyExcludeId = (int) ($exclude['family_id'] ?? 0);
        $memberExcludeId = (int) ($exclude['member_id'] ?? 0);
        $childExcludeId = (int) ($exclude['child_id'] ?? 0);
        $personExcludeId = (int) ($exclude['person_id'] ?? 0);

        $sql = 'SELECT source_table, source_id, source_name
                FROM (
                    SELECT
                        \'families\' AS source_table,
                        f.id AS source_id,
                        f.responsible_name AS source_name
                    FROM families f
                    WHERE f.cpf_responsible = :cpf_families
                      AND f.id <> COALESCE(NULLIF(:exclude_family_id_families, 0), -1)

                    UNION ALL

                    SELECT
                        \'family_members\' AS source_table,
                        fm.id AS source_id,
                        fm.name AS source_name
                    FROM family_members fm
                    WHERE fm.cpf = :cpf_members
                      AND fm.id <> COALESCE(NULLIF(:exclude_member_id_members, 0), -1)

                    UNION ALL

                    SELECT
                        \'children\' AS source_table,
                        c.id AS source_id,
                        c.name AS source_name
                    FROM children c
                    WHERE c.cpf = :cpf_children
                      AND c.id <> COALESCE(NULLIF(:exclude_child_id_children, 0), -1)

                    UNION ALL

                    SELECT
                        \'people\' AS source_table,
                        p.id AS source_id,
                        COALESCE(p.full_name, p.social_name, CONCAT(\'Pessoa #\', p.id)) AS source_name
                    FROM people p
                    WHERE p.cpf = :cpf_people
                      AND p.id <> COALESCE(NULLIF(:exclude_person_id_people, 0), -1)
                ) AS conflicts
                LIMIT 1';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'cpf_families' => $cpfFormatted,
            'cpf_members' => $cpfFormatted,
            'cpf_children' => $cpfFormatted,
            'cpf_people' => $cpfFormatted,
            'exclude_family_id_families' => $familyExcludeId,
            'exclude_member_id_members' => $memberExcludeId,
            'exclude_child_id_children' => $childExcludeId,
            'exclude_person_id_people' => $personExcludeId,
        ]);

        $row = $stmt->fetch();
        return is_array($row) ? $row : null;
    }

    public function deleteMember(int $memberId, int $familyId): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM family_members WHERE id = :id AND family_id = :family_id');
        $stmt->execute([
            'id' => $memberId,
            'family_id' => $familyId,
        ]);
    }

    public function recalculateFamilyIndicators(int $familyId): void
    {
        $family = $this->findById($familyId);
        if ($family === null) {
            return;
        }

        $stmt = $this->pdo->prepare(
            'SELECT
                COALESCE(SUM(CASE WHEN works = 1 THEN 1 ELSE 0 END), 0) AS workers_count,
                COALESCE(SUM(income), 0) AS total_income,
                COUNT(*) AS member_count,
                COALESCE(SUM(
                    CASE
                        WHEN birth_date IS NOT NULL AND birth_date <= DATE_SUB(CURDATE(), INTERVAL 18 YEAR) THEN 1
                        ELSE 0
                    END
                ), 0) AS adults_count
             FROM family_members
             WHERE family_id = :family_id'
        );
        $stmt->execute(['family_id' => $familyId]);
        $summary = $stmt->fetch();
        if (!is_array($summary)) {
            return;
        }

        $childrenStmt = $this->pdo->prepare(
            'SELECT COUNT(*) AS total_children
             FROM children
             WHERE family_id = :family_id'
        );
        $childrenStmt->execute(['family_id' => $familyId]);
        $children = $childrenStmt->fetch();
        $childrenCount = (int) ($children['total_children'] ?? 0);

        $update = $this->pdo->prepare(
            'UPDATE families
             SET adults_count = :adults_count,
                 workers_count = :workers_count,
                 family_income_total = :family_income_total,
                 family_income_average = :family_income_average,
                 children_count = :children_count
             WHERE id = :id'
        );
        $update->execute([
            'id' => $familyId,
            'adults_count' => ((string) ($family['birth_date'] ?? '') !== '' && (string) ($family['birth_date'] ?? '') <= date('Y-m-d', strtotime('-18 years')))
                ? (int) ($summary['adults_count'] ?? 0) + 1
                : (int) ($summary['adults_count'] ?? 0),
            'workers_count' => (int) ($summary['workers_count'] ?? 0) + ((int) ($family['responsible_works'] ?? 0) === 1 ? 1 : 0),
            'family_income_total' => number_format((float) ($summary['total_income'] ?? 0) + (float) ($family['responsible_income'] ?? 0), 2, '.', ''),
            'family_income_average' => number_format(
                (
                    (float) ($summary['total_income'] ?? 0)
                    + (float) ($family['responsible_income'] ?? 0)
                ) / max(1, 1 + (int) ($summary['member_count'] ?? 0) + $childrenCount),
                2,
                '.',
                ''
            ),
            'children_count' => $childrenCount,
        ]);
    }

    public function countChildren(int $familyId): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) AS total_children
             FROM children
             WHERE family_id = :family_id'
        );
        $stmt->execute(['family_id' => $familyId]);
        $row = $stmt->fetch();
        return (int) ($row['total_children'] ?? 0);
    }

    public function updateIndicators(int $familyId, array $data): void
    {
        $data['id'] = $familyId;

        $stmt = $this->pdo->prepare(
            'UPDATE families
             SET adults_count = :adults_count,
                 workers_count = :workers_count,
                 family_income_total = :family_income_total,
                 family_income_average = :family_income_average,
                 children_count = :children_count
             WHERE id = :id'
        );
        $stmt->execute($data);
    }

    public function listFamilyMembersSummary(int $familyId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, name, relationship, birth_date, studies, works, income
             FROM family_members
             WHERE family_id = :family_id
             ORDER BY name ASC, id ASC'
        );
        $stmt->execute(['family_id' => $familyId]);
        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    private function buildFilterSql(array $filters, array &$params): string
    {
        $sql = '';

        $q = trim((string) ($filters['q'] ?? ''));
        if ($q !== '') {
            $sql .= ' AND (
                responsible_name LIKE :q_responsible
                OR cpf_responsible LIKE :q_cpf
                OR neighborhood LIKE :q_neighborhood
                OR city LIKE :q_city
            )';
            $like = '%' . $q . '%';
            $params['q_responsible'] = $like;
            $params['q_cpf'] = $like;
            $params['q_neighborhood'] = $like;
            $params['q_city'] = $like;
        }

        $city = trim((string) ($filters['city'] ?? ''));
        if ($city !== '') {
            $sql .= ' AND city = :city';
            $params['city'] = $city;
        }

        $status = trim((string) ($filters['status'] ?? ''));
        if ($status !== '') {
            $sql .= ' AND is_active = :is_active';
            $params['is_active'] = $status === 'ativo' ? 1 : 0;
        }

        $docStatus = trim((string) ($filters['documentation_status'] ?? ''));
        if ($docStatus !== '') {
            $sql .= ' AND documentation_status = :documentation_status';
            $params['documentation_status'] = $docStatus;
        }

        return $sql;
    }
}
