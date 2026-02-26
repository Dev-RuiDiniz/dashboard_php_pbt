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
                    is_homeless, homeless_time, stay_location, work_interest, created_at, updated_at
                FROM people
                WHERE 1=1';
        $params = [];

        $q = trim((string) ($filters['q'] ?? ''));
        if ($q !== '') {
            $sql .= ' AND (
                full_name LIKE :q
                OR social_name LIKE :q
                OR cpf LIKE :q
                OR rg LIKE :q
                OR stay_location LIKE :q
            )';
            $params['q'] = '%' . $q . '%';
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
                is_homeless, homeless_time, stay_location, has_family_in_region, family_contact,
                education_level, profession_skills, formal_work_history, work_interest, work_interest_detail
            ) VALUES (
                :full_name, :social_name, :cpf, :rg, :birth_date, :approx_age, :gender,
                :is_homeless, :homeless_time, :stay_location, :has_family_in_region, :family_contact,
                :education_level, :profession_skills, :formal_work_history, :work_interest, :work_interest_detail
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
                has_family_in_region = :has_family_in_region,
                family_contact = :family_contact,
                education_level = :education_level,
                profession_skills = :profession_skills,
                formal_work_history = :formal_work_history,
                work_interest = :work_interest,
                work_interest_detail = :work_interest_detail
             WHERE id = :id'
        );
        $stmt->execute($data);
    }
}

