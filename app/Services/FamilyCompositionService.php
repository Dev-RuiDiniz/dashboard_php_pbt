<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ChildModel;
use App\Models\FamilyModel;
use Throwable;

final class FamilyCompositionService
{
    public const PERSON_TYPES = ['principal', 'member', 'child'];

    public function __construct(
        private readonly FamilyModel $familyModel,
        private readonly ChildModel $childModel,
        private readonly FamilyIndicatorsService $indicatorsService,
    ) {
    }

    public function sanitizePersonType(string $value, string $default = ''): string
    {
        $value = strtolower(trim($value));
        if (in_array($value, self::PERSON_TYPES, true)) {
            return $value;
        }

        return $default;
    }

    public function defaultPrincipalFormData(array $family): array
    {
        return [
            'responsible_name' => (string) ($family['responsible_name'] ?? ''),
            'cpf_responsible' => (string) ($family['cpf_responsible'] ?? ''),
            'rg_responsible' => (string) ($family['rg_responsible'] ?? ''),
            'birth_date' => (string) ($family['birth_date'] ?? ''),
            'phone' => (string) ($family['phone'] ?? ''),
            'responsible_works' => (int) ($family['responsible_works'] ?? 0),
            'responsible_income' => number_format((float) ($family['responsible_income'] ?? 0), 2, '.', ''),
            'person_type' => 'principal',
        ];
    }

    public function defaultMemberFormData(int $familyId): array
    {
        return [
            'family_id' => $familyId,
            'name' => '',
            'relationship' => '',
            'cpf' => '',
            'rg' => '',
            'birth_date' => '',
            'studies' => 0,
            'works' => 0,
            'income' => '0.00',
            'person_type' => 'member',
        ];
    }

    public function defaultChildFormData(int $familyId): array
    {
        return [
            'family_id' => $familyId,
            'name' => '',
            'cpf' => '',
            'rg' => '',
            'birth_date' => '',
            'age_years' => '',
            'relationship' => '',
            'studies' => 0,
            'notes' => '',
            'person_type' => 'child',
        ];
    }

    public function resolveMemberPersonType(array $member): string
    {
        return 'member';
    }

    public function sanitizePrincipalInput(array $post): array
    {
        return [
            'responsible_name' => trim((string) ($post['responsible_name'] ?? '')),
            'cpf_responsible' => trim((string) ($post['cpf_responsible'] ?? '')),
            'rg_responsible' => FamilyDataSupport::sanitizeRg((string) ($post['rg_responsible'] ?? '')),
            'birth_date' => trim((string) ($post['birth_date'] ?? '')),
            'phone' => FamilyDataSupport::sanitizePhone((string) ($post['phone'] ?? '')),
            'responsible_works' => isset($post['responsible_works']) ? 1 : 0,
            'responsible_income' => FamilyDataSupport::sanitizeMoney((string) ($post['responsible_income'] ?? '0')),
            'person_type' => 'principal',
        ];
    }

    public function sanitizeMemberInput(array $post, int $familyId): array
    {
        return [
            'family_id' => $familyId,
            'name' => trim((string) ($post['name'] ?? '')),
            'relationship' => trim((string) ($post['relationship'] ?? '')),
            'cpf' => trim((string) ($post['cpf'] ?? '')),
            'rg' => FamilyDataSupport::sanitizeRg((string) ($post['rg'] ?? '')),
            'birth_date' => trim((string) ($post['birth_date'] ?? '')),
            'studies' => isset($post['studies']) ? 1 : 0,
            'works' => isset($post['works']) ? 1 : 0,
            'income' => FamilyDataSupport::sanitizeMoney((string) ($post['income'] ?? '0')),
            'person_type' => $this->sanitizePersonType((string) ($post['person_type'] ?? ''), 'member'),
        ];
    }

    public function sanitizeChildInput(array $post, int $familyId): array
    {
        return [
            'family_id' => $familyId,
            'name' => trim((string) ($post['name'] ?? '')),
            'cpf' => trim((string) ($post['cpf'] ?? '')),
            'rg' => FamilyDataSupport::sanitizeRg((string) ($post['rg'] ?? '')),
            'birth_date' => trim((string) ($post['birth_date'] ?? '')),
            'age_years' => null,
            'relationship' => trim((string) ($post['relationship'] ?? '')),
            'studies' => isset($post['studies']) ? 1 : 0,
            'notes' => trim((string) ($post['notes'] ?? '')),
            'person_type' => 'child',
        ];
    }

    public function validatePrincipalInput(array &$input, int $familyId): ?string
    {
        if (trim((string) ($input['responsible_name'] ?? '')) === '') {
            return 'Nome do responsavel principal e obrigatorio.';
        }

        if (trim((string) ($input['cpf_responsible'] ?? '')) === '') {
            return 'CPF do responsavel principal e obrigatorio.';
        }

        $rg = trim((string) ($input['rg_responsible'] ?? ''));
        if ($rg !== '' && !FamilyDataSupport::isRgValid($rg)) {
            return 'RG invalido. Use o formato 00.000.000-0.';
        }

        if (!is_numeric((string) ($input['responsible_income'] ?? '0'))) {
            return 'Renda do responsavel principal invalida.';
        }

        if (!CpfService::isValid((string) ($input['cpf_responsible'] ?? ''))) {
            return 'CPF invalido.';
        }

        $input['cpf_responsible'] = (string) CpfService::format((string) $input['cpf_responsible']);

        try {
            $conflict = $this->familyModel->findCpfConflict((string) $input['cpf_responsible'], [
                'family_id' => $familyId,
            ]);
        } catch (Throwable) {
            return 'Falha ao validar duplicidade de CPF.';
        }

        if ($conflict !== null) {
            return FamilyDataSupport::buildCpfConflictMessage($conflict);
        }

        return null;
    }

    public function validateMemberInput(array &$input, ?int $memberId): ?string
    {
        if (trim((string) ($input['name'] ?? '')) === '') {
            return 'Nome do membro e obrigatorio.';
        }

        if (trim((string) ($input['cpf'] ?? '')) === '') {
            return 'CPF do membro familiar e obrigatorio.';
        }

        $rg = trim((string) ($input['rg'] ?? ''));
        if ($rg !== '' && !FamilyDataSupport::isRgValid($rg)) {
            return 'RG invalido. Use o formato 00.000.000-0.';
        }

        if (!CpfService::isValid((string) ($input['cpf'] ?? ''))) {
            return 'CPF invalido.';
        }

        if (!is_numeric((string) ($input['income'] ?? '0'))) {
            return 'Renda do membro invalida.';
        }

        $input['cpf'] = (string) CpfService::format((string) $input['cpf']);

        try {
            $conflict = $this->familyModel->findCpfConflict((string) $input['cpf'], [
                'member_id' => $memberId ?? 0,
            ]);
        } catch (Throwable) {
            return 'Falha ao validar duplicidade de CPF.';
        }

        if ($conflict !== null) {
            return FamilyDataSupport::buildCpfConflictMessage($conflict);
        }

        return null;
    }

    public function validateChildInput(array &$input, ?int $childId): ?string
    {
        if ((int) ($input['family_id'] ?? 0) <= 0) {
            return 'Familia invalida para crianca.';
        }

        if (trim((string) ($input['name'] ?? '')) === '') {
            return 'Nome da crianca e obrigatorio.';
        }

        $cpf = trim((string) ($input['cpf'] ?? ''));
        if ($cpf !== '') {
            if (!CpfService::isValid($cpf)) {
                return 'CPF invalido.';
            }

            $input['cpf'] = (string) CpfService::format($cpf);

            try {
                $conflict = $this->familyModel->findCpfConflict((string) $input['cpf'], [
                    'child_id' => $childId ?? 0,
                ]);
            } catch (Throwable) {
                return 'Falha ao validar duplicidade de CPF.';
            }

            if ($conflict !== null) {
                return FamilyDataSupport::buildCpfConflictMessage($conflict);
            }
        }

        $rg = trim((string) ($input['rg'] ?? ''));
        if ($rg !== '' && !FamilyDataSupport::isRgValid($rg)) {
            return 'RG invalido. Use o formato 00.000.000-0.';
        }

        return null;
    }

    public function updatePrincipal(int $familyId, array $input): void
    {
        $this->familyModel->updateResponsible($familyId, [
            'responsible_name' => $input['responsible_name'],
            'cpf_responsible' => $input['cpf_responsible'] !== '' ? $input['cpf_responsible'] : null,
            'rg_responsible' => $input['rg_responsible'] !== '' ? $input['rg_responsible'] : null,
            'birth_date' => $input['birth_date'] !== '' ? $input['birth_date'] : null,
            'phone' => $input['phone'] !== '' ? $input['phone'] : null,
            'responsible_works' => (int) $input['responsible_works'],
            'responsible_income' => $input['responsible_income'],
        ]);
        $this->indicatorsService->recalculate($familyId);
    }

    public function createMember(array $input): void
    {
        $this->applyMemberPersonTypeRules($input);
        $this->familyModel->createMember($this->toMemberPersistenceData($input));
        $this->indicatorsService->recalculate((int) $input['family_id']);
    }

    public function updateMember(int $memberId, array $input): void
    {
        $this->applyMemberPersonTypeRules($input);
        $this->familyModel->updateMember($memberId, $this->toMemberPersistenceData($input));
        $this->indicatorsService->recalculate((int) $input['family_id']);
    }

    public function deleteMember(int $memberId, int $familyId): void
    {
        $this->familyModel->deleteMember($memberId, $familyId);
        $this->indicatorsService->recalculate($familyId);
    }

    public function createChild(array $input): void
    {
        $this->childModel->create($this->toChildPersistenceData($input));
        $this->indicatorsService->recalculate((int) $input['family_id']);
    }

    public function updateChild(int $childId, array $input): void
    {
        $this->childModel->update($childId, $this->toChildPersistenceData($input));
        $this->indicatorsService->recalculate((int) $input['family_id']);
    }

    public function deleteChild(int $childId, int $familyId): void
    {
        $this->childModel->delete($childId);
        $this->indicatorsService->recalculate($familyId);
    }

    private function applyMemberPersonTypeRules(array &$input): void
    {
        $input['person_type'] = 'member';
        if (!FamilyDataSupport::isAdult((string) ($input['birth_date'] ?? ''))) {
            $input['works'] = 0;
        }
    }

    private function toMemberPersistenceData(array $input): array
    {
        return [
            'family_id' => (int) $input['family_id'],
            'name' => $input['name'],
            'relationship' => $input['relationship'] !== '' ? $input['relationship'] : null,
            'cpf' => $input['cpf'] !== '' ? $input['cpf'] : null,
            'rg' => $input['rg'] !== '' ? $input['rg'] : null,
            'birth_date' => $input['birth_date'] !== '' ? $input['birth_date'] : null,
            'studies' => (int) ($input['studies'] ?? 0),
            'works' => (int) $input['works'],
            'income' => $input['income'],
        ];
    }

    private function toChildPersistenceData(array $input): array
    {
        $birthDate = ($input['birth_date'] ?? '') !== '' ? (string) $input['birth_date'] : null;
        $ageYears = FamilyDataSupport::calculateAgeFromBirthDate($birthDate);

        return [
            'family_id' => (int) $input['family_id'],
            'name' => $input['name'],
            'cpf' => ($input['cpf'] ?? '') !== '' ? $input['cpf'] : null,
            'rg' => ($input['rg'] ?? '') !== '' ? $input['rg'] : null,
            'birth_date' => $birthDate,
            'age_years' => $ageYears,
            'relationship' => ($input['relationship'] ?? '') !== '' ? $input['relationship'] : null,
            'studies' => (int) ($input['studies'] ?? 0),
            'notes' => ($input['notes'] ?? '') !== '' ? $input['notes'] : null,
        ];
    }
}
