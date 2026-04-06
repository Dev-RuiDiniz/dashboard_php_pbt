<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class PersonModel
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function search(array $filters): array
    {
        $sql = 'SELECT
                    id, full_name, social_name, cpf, rg, birth_date, approx_age, gender,
                    phone,
                    is_homeless, homeless_time, stay_location, work_interest, created_at, updated_at
                FROM people
                WHERE 1=1';
        $params = [];

        $q = trim((string) ($filters['q'] ?? ''));
        if ($q !== '') {
            $cpfDigits = \App\Services\CpfService::digits($q);
            $sql .= ' AND (
                full_name LIKE :q_full_name
                OR social_name LIKE :q_social_name
                OR cpf LIKE :q_cpf
                OR rg LIKE :q_rg
                OR stay_location LIKE :q_stay
            ';
            if ($cpfDigits !== '') {
                $sql .= '
                OR REPLACE(REPLACE(REPLACE(COALESCE(cpf, \'\'), \'.\', \'\'), \'-\', \'\'), \' \', \'\') LIKE :q_cpf_digits';
            }
            $sql .= '
            )';
            $like = '%' . $q . '%';
            $params['q_full_name'] = $like;
            $params['q_social_name'] = $like;
            $params['q_cpf'] = $like;
            if ($cpfDigits !== '') {
                $params['q_cpf_digits'] = '%' . $cpfDigits . '%';
            }
            $params['q_rg'] = $like;
            $params['q_stay'] = $like;
        }

        $isHomeless = trim((string) ($filters['is_homeless'] ?? ''));
        if ($isHomeless !== '') {
            $sql .= ' AND is_homeless = :is_homeless';
            $params['is_homeless'] = $isHomeless === '1' ? 1 : 0;
        }

        $workInterest = trim((string) ($filters['work_interest'] ?? ''));
        if ($workInterest !== '') {
            $sql .= ' AND work_interest = :work_interest';
            $params['work_interest'] = $workInterest === '1' ? 1 : 0;
        }

        $sql .= ' ORDER BY COALESCE(full_name, social_name) ASC, id DESC LIMIT 300';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM people WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return is_array($row) ? $row : null;
    }

    public function getPhones(int $personId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, person_id, number, label, sort_order, is_primary, created_at, updated_at
             FROM person_phones
             WHERE person_id = :person_id
             ORDER BY is_primary DESC, sort_order ASC, id ASC'
        );
        $stmt->execute(['person_id' => $personId]);
        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function replacePhones(int $personId, array $phones): void
    {
        $delete = $this->pdo->prepare('DELETE FROM person_phones WHERE person_id = :person_id');
        $delete->execute(['person_id' => $personId]);

        if ($phones === []) {
            return;
        }

        $insert = $this->pdo->prepare(
            'INSERT INTO person_phones (person_id, number, label, sort_order, is_primary)
             VALUES (:person_id, :number, :label, :sort_order, :is_primary)'
        );

        foreach ($phones as $phone) {
            $insert->execute([
                'person_id' => $personId,
                'number' => $phone['number'],
                'label' => ($phone['label'] ?? '') !== '' ? $phone['label'] : null,
                'sort_order' => (int) ($phone['sort_order'] ?? 0),
                'is_primary' => (int) ($phone['is_primary'] ?? 0),
            ]);
        }
    }

    public function findByCpfExcludingId(string $cpfFormatted, ?int $excludeId = null): ?array
    {
        $sql = 'SELECT id, cpf, full_name, social_name FROM people WHERE cpf = :cpf';
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
            'INSERT INTO people (
                full_name, social_name, cpf, rg, birth_date, approx_age, gender,
                is_homeless, homeless_time, stay_location, phone, previous_address, has_family_in_region, family_contact,
                education_level, profession_skills, formal_work_history, work_interest, work_interest_detail
                , chronic_disease, chronic_disease_other_details, has_physical_disability, physical_disability_details,
                uses_continuous_medication, continuous_medication_details, has_addiction, addiction_details, social_benefit
            ) VALUES (
                :full_name, :social_name, :cpf, :rg, :birth_date, :approx_age, :gender,
                :is_homeless, :homeless_time, :stay_location, :phone, :previous_address, :has_family_in_region, :family_contact,
                :education_level, :profession_skills, :formal_work_history, :work_interest, :work_interest_detail
                , :chronic_disease, :chronic_disease_other_details, :has_physical_disability, :physical_disability_details,
                :uses_continuous_medication, :continuous_medication_details, :has_addiction, :addiction_details, :social_benefit
            )'
        );
        $stmt->execute($data);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $data['id'] = $id;
        $stmt = $this->pdo->prepare(
            'UPDATE people SET
                full_name = :full_name,
                social_name = :social_name,
                cpf = :cpf,
                rg = :rg,
                birth_date = :birth_date,
                approx_age = :approx_age,
                gender = :gender,
                is_homeless = :is_homeless,
                homeless_time = :homeless_time,
                stay_location = :stay_location,
                phone = :phone,
                previous_address = :previous_address,
                has_family_in_region = :has_family_in_region,
                family_contact = :family_contact,
                education_level = :education_level,
                profession_skills = :profession_skills,
                formal_work_history = :formal_work_history,
                work_interest = :work_interest,
                work_interest_detail = :work_interest_detail,
                chronic_disease = :chronic_disease,
                chronic_disease_other_details = :chronic_disease_other_details,
                has_physical_disability = :has_physical_disability,
                physical_disability_details = :physical_disability_details,
                uses_continuous_medication = :uses_continuous_medication,
                continuous_medication_details = :continuous_medication_details,
                has_addiction = :has_addiction,
                addiction_details = :addiction_details,
                social_benefit = :social_benefit
             WHERE id = :id'
        );
        $stmt->execute($data);
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM people WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
