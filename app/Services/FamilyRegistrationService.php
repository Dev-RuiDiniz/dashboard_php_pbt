<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\FamilyModel;
use Throwable;

final class FamilyRegistrationService
{
    public const DOC_STATUSES = ['ok', 'pendente', 'parcial'];
    public const HOUSING_TYPES = [
        'propria' => 'Propria',
        'alugada' => 'Alugada',
        'cedida' => 'Cedida',
        'financiada' => 'Financiada',
        'ocupacao' => 'Ocupacao',
        'situacao_rua' => 'Situacao de rua',
        'outro' => 'Outro',
    ];
    public const MARITAL_STATUSES = [
        'solteiro' => 'Solteiro(a)',
        'casado' => 'Casado(a)',
        'uniao_estavel' => 'Uniao estavel',
        'separado' => 'Separado(a)',
        'divorciado' => 'Divorciado(a)',
        'viuvo' => 'Viuvo(a)',
    ];
    public const EDUCATION_LEVELS = [
        'analfabeto' => 'Analfabeto(a)',
        'fundamental_incompleto' => 'Fundamental incompleto',
        'fundamental_completo' => 'Fundamental completo',
        'medio_incompleto' => 'Medio incompleto',
        'medio_completo' => 'Medio completo',
        'superior_incompleto' => 'Superior incompleto',
        'superior_completo' => 'Superior completo',
    ];
    public const PROFESSIONAL_STATUSES = [
        'desempregado' => 'Desempregado(a)',
        'empregado' => 'Empregado(a)',
        'autonomo' => 'Autonomo(a)',
        'informal' => 'Trabalho informal',
        'aposentado' => 'Aposentado(a)',
        'afastado' => 'Afastado(a)',
        'do_lar' => 'Do lar',
    ];

    public function __construct(
        private readonly FamilyModel $familyModel,
        private readonly FamilyIndicatorsService $indicatorsService,
    ) {
    }

    public function defaultFormData(): array
    {
        return [
            'responsible_name' => '',
            'cpf_responsible' => '',
            'rg_responsible' => '',
            'birth_date' => '',
            'phone' => '',
            'responsible_works' => 0,
            'responsible_income' => '0.00',
            'marital_status' => '',
            'education_level' => '',
            'professional_status' => '',
            'profession_detail' => '',
            'cep' => '',
            'address' => '',
            'address_number' => '',
            'address_complement' => '',
            'neighborhood' => '',
            'city' => '',
            'state' => '',
            'location_reference' => '',
            'housing_type' => '',
            'adults_count' => 0,
            'workers_count' => 0,
            'family_income_total' => '0.00',
            'family_income_average' => '0.00',
            'children_count' => 0,
            'documentation_status' => 'ok',
            'documentation_notes' => '',
            'needs_visit' => 0,
            'general_notes' => '',
            'is_active' => 1,
        ];
    }

    public function sanitize(array $post): array
    {
        return [
            'responsible_name' => trim((string) ($post['responsible_name'] ?? '')),
            'cpf_responsible' => trim((string) ($post['cpf_responsible'] ?? '')),
            'rg_responsible' => FamilyDataSupport::sanitizeRg((string) ($post['rg_responsible'] ?? '')),
            'birth_date' => trim((string) ($post['birth_date'] ?? '')),
            'phone' => FamilyDataSupport::sanitizePhone((string) ($post['phone'] ?? '')),
            'responsible_works' => isset($post['responsible_works']) ? 1 : 0,
            'responsible_income' => FamilyDataSupport::sanitizeMoney((string) ($post['responsible_income'] ?? '0')),
            'marital_status' => trim((string) ($post['marital_status'] ?? '')),
            'education_level' => trim((string) ($post['education_level'] ?? '')),
            'professional_status' => trim((string) ($post['professional_status'] ?? '')),
            'profession_detail' => trim((string) ($post['profession_detail'] ?? '')),
            'cep' => trim((string) ($post['cep'] ?? '')),
            'address' => trim((string) ($post['address'] ?? '')),
            'address_number' => trim((string) ($post['address_number'] ?? '')),
            'address_complement' => trim((string) ($post['address_complement'] ?? '')),
            'neighborhood' => trim((string) ($post['neighborhood'] ?? '')),
            'city' => trim((string) ($post['city'] ?? '')),
            'state' => strtoupper(substr(trim((string) ($post['state'] ?? '')), 0, 2)),
            'location_reference' => trim((string) ($post['location_reference'] ?? '')),
            'housing_type' => trim((string) ($post['housing_type'] ?? '')),
            'documentation_status' => trim((string) ($post['documentation_status'] ?? 'ok')),
            'documentation_notes' => trim((string) ($post['documentation_notes'] ?? '')),
            'needs_visit' => isset($post['needs_visit']) ? 1 : 0,
            'general_notes' => trim((string) ($post['general_notes'] ?? '')),
            'is_active' => isset($post['is_active']) ? 1 : 0,
        ];
    }

    public function validate(array &$input, ?array $existingFamily, ?int $excludeId): ?string
    {
        if (trim((string) ($input['responsible_name'] ?? '')) === '') {
            return 'Nome do responsavel principal e obrigatorio.';
        }

        if (trim((string) ($input['cpf_responsible'] ?? '')) === '') {
            return 'CPF do responsavel principal e obrigatorio.';
        }

        if (trim((string) ($input['rg_responsible'] ?? '')) === '') {
            return 'RG do responsavel principal e obrigatorio.';
        }

        if (!FamilyDataSupport::isRgValid((string) ($input['rg_responsible'] ?? ''))) {
            return 'RG invalido. Use o formato 00.000.000-0.';
        }

        $fieldConfig = [
            'housing_type' => ['options' => array_keys(self::HOUSING_TYPES), 'label' => 'Tipo de moradia'],
            'marital_status' => ['options' => array_keys(self::MARITAL_STATUSES), 'label' => 'Estado civil'],
            'education_level' => ['options' => array_keys(self::EDUCATION_LEVELS), 'label' => 'Escolaridade'],
            'professional_status' => ['options' => array_keys(self::PROFESSIONAL_STATUSES), 'label' => 'Situacao profissional'],
        ];

        foreach ($fieldConfig as $field => $config) {
            $value = trim((string) ($input[$field] ?? ''));
            if ($value === '') {
                continue;
            }

            $options = is_array($config['options'] ?? null) ? $config['options'] : [];
            if (in_array($value, $options, true)) {
                continue;
            }

            $legacyValue = trim((string) ($existingFamily[$field] ?? ''));
            if ($legacyValue !== '' && $legacyValue === $value) {
                continue;
            }

            return (string) ($config['label'] ?? 'Campo') . ' invalido.';
        }

        if (!is_numeric((string) ($input['responsible_income'] ?? '0'))) {
            return 'Renda do responsavel principal invalida.';
        }

        if (!\App\Services\CpfService::isValid((string) ($input['cpf_responsible'] ?? ''))) {
            return 'CPF invalido.';
        }

        $input['cpf_responsible'] = (string) \App\Services\CpfService::format((string) $input['cpf_responsible']);

        try {
            $conflict = $this->familyModel->findCpfConflict((string) $input['cpf_responsible'], [
                'family_id' => $excludeId ?? 0,
            ]);
        } catch (Throwable) {
            return 'Falha ao validar duplicidade de CPF.';
        }

        if ($conflict !== null) {
            return FamilyDataSupport::buildCpfConflictMessage($conflict);
        }

        return null;
    }

    public function create(array $input): int
    {
        $familyId = $this->familyModel->create($this->toPersistenceData($input));
        $this->indicatorsService->recalculate($familyId);
        return $familyId;
    }

    public function update(int $familyId, array $input): void
    {
        $this->familyModel->update($familyId, $this->toPersistenceData($input));
        $this->indicatorsService->recalculate($familyId);
    }

    public function withLegacyOption(array $baseOptions, string $selectedValue): array
    {
        if ($selectedValue === '' || isset($baseOptions[$selectedValue])) {
            return $baseOptions;
        }

        $baseOptions[$selectedValue] = 'Legado: ' . $selectedValue;
        return $baseOptions;
    }

    private function toPersistenceData(array $input): array
    {
        if (!in_array($input['documentation_status'], self::DOC_STATUSES, true)) {
            $input['documentation_status'] = 'ok';
        }

        return [
            'responsible_name' => $input['responsible_name'],
            'cpf_responsible' => $input['cpf_responsible'] !== '' ? $input['cpf_responsible'] : null,
            'rg_responsible' => $input['rg_responsible'] !== '' ? $input['rg_responsible'] : null,
            'birth_date' => $input['birth_date'] !== '' ? $input['birth_date'] : null,
            'phone' => $input['phone'] !== '' ? $input['phone'] : null,
            'responsible_works' => (int) $input['responsible_works'],
            'responsible_income' => $input['responsible_income'],
            'marital_status' => $input['marital_status'] !== '' ? $input['marital_status'] : null,
            'education_level' => $input['education_level'] !== '' ? $input['education_level'] : null,
            'professional_status' => $input['professional_status'] !== '' ? $input['professional_status'] : null,
            'profession_detail' => $input['profession_detail'] !== '' ? $input['profession_detail'] : null,
            'cep' => $input['cep'] !== '' ? $input['cep'] : null,
            'address' => $input['address'] !== '' ? $input['address'] : null,
            'address_number' => $input['address_number'] !== '' ? $input['address_number'] : null,
            'address_complement' => $input['address_complement'] !== '' ? $input['address_complement'] : null,
            'neighborhood' => $input['neighborhood'] !== '' ? $input['neighborhood'] : null,
            'city' => $input['city'] !== '' ? $input['city'] : null,
            'state' => $input['state'] !== '' ? $input['state'] : null,
            'location_reference' => $input['location_reference'] !== '' ? $input['location_reference'] : null,
            'housing_type' => $input['housing_type'] !== '' ? $input['housing_type'] : null,
            'documentation_status' => $input['documentation_status'],
            'documentation_notes' => $input['documentation_notes'] !== '' ? $input['documentation_notes'] : null,
            'needs_visit' => (int) $input['needs_visit'],
            'general_notes' => $input['general_notes'] !== '' ? $input['general_notes'] : null,
            'is_active' => (int) $input['is_active'],
        ];
    }
}
