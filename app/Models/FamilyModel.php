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
                    neighborhood, city, state, family_income_total,
                    children_count, documentation_status, needs_visit, is_active, updated_at
                FROM families
                WHERE 1=1';
        $params = [];

        $q = trim((string) ($filters['q'] ?? ''));
        if ($q !== '') {
            $sql .= ' AND (responsible_name LIKE :q OR cpf_responsible LIKE :q OR neighborhood LIKE :q OR city LIKE :q)';
            $params['q'] = '%' . $q . '%';
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

        $sql .= ' ORDER BY responsible_name ASC, id DESC LIMIT 200';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM families WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return is_array($row) ? $row : null;
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
                marital_status, education_level, professional_status, profession_detail,
                cep, address, address_number, address_complement, neighborhood, city, state,
                location_reference, housing_type, adults_count, workers_count,
                family_income_total, children_count, documentation_status,
                documentation_notes, needs_visit, general_notes, is_active
            ) VALUES (
                :responsible_name, :cpf_responsible, :rg_responsible, :birth_date, :phone,
                :marital_status, :education_level, :professional_status, :profession_detail,
                :cep, :address, :address_number, :address_complement, :neighborhood, :city, :state,
                :location_reference, :housing_type, :adults_count, :workers_count,
                :family_income_total, :children_count, :documentation_status,
                :documentation_notes, :needs_visit, :general_notes, :is_active
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
                adults_count = :adults_count,
                workers_count = :workers_count,
                family_income_total = :family_income_total,
                children_count = :children_count,
                documentation_status = :documentation_status,
                documentation_notes = :documentation_notes,
                needs_visit = :needs_visit,
                general_notes = :general_notes,
                is_active = :is_active
            WHERE id = :id'
        );

        $stmt->execute($data);
    }
}

