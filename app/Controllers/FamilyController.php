<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Container;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\FamilyModel;
use App\Models\ChildModel;
use App\Services\CpfService;
use PDO;
use Throwable;

final class FamilyController
{
    private const DOC_STATUSES = ['ok', 'pendente', 'parcial'];
    private const HOUSING_TYPES = [
        'propria' => 'Propria',
        'alugada' => 'Alugada',
        'cedida' => 'Cedida',
        'financiada' => 'Financiada',
        'ocupacao' => 'Ocupacao',
        'situacao_rua' => 'Situacao de rua',
        'outro' => 'Outro',
    ];
    private const MARITAL_STATUSES = [
        'solteiro' => 'Solteiro(a)',
        'casado' => 'Casado(a)',
        'uniao_estavel' => 'Uniao estavel',
        'separado' => 'Separado(a)',
        'divorciado' => 'Divorciado(a)',
        'viuvo' => 'Viuvo(a)',
    ];
    private const EDUCATION_LEVELS = [
        'analfabeto' => 'Analfabeto(a)',
        'fundamental_incompleto' => 'Fundamental incompleto',
        'fundamental_completo' => 'Fundamental completo',
        'medio_incompleto' => 'Medio incompleto',
        'medio_completo' => 'Medio completo',
        'superior_incompleto' => 'Superior incompleto',
        'superior_completo' => 'Superior completo',
    ];
    private const PROFESSIONAL_STATUSES = [
        'desempregado' => 'Desempregado(a)',
        'empregado' => 'Empregado(a)',
        'autonomo' => 'Autonomo(a)',
        'informal' => 'Trabalho informal',
        'aposentado' => 'Aposentado(a)',
        'afastado' => 'Afastado(a)',
        'do_lar' => 'Do lar',
    ];

    public function __construct(private readonly Container $container)
    {
    }

    public function index(): void
    {
        $filters = [
            'q' => trim((string) ($_GET['q'] ?? '')),
            'city' => trim((string) ($_GET['city'] ?? '')),
            'status' => trim((string) ($_GET['status'] ?? '')),
            'documentation_status' => trim((string) ($_GET['documentation_status'] ?? '')),
        ];

        try {
            $families = $this->familyModel()->search($filters);
            $filteredTotal = $this->familyModel()->count($filters);
            $overallTotal = $this->familyModel()->count();
        } catch (Throwable $exception) {
            $families = [];
            $filteredTotal = 0;
            $overallTotal = 0;
        }

        View::render('families.index', [
            '_layout' => 'layouts.app',
            'appName' => (string) ($this->container->get('config')['app']['name'] ?? 'Dashboard PHP PBT'),
            'pageTitle' => 'Familias',
            'activeMenu' => 'familias',
            'authUser' => Session::get('auth_user', []),
            'families' => $families,
            'filters' => $filters,
            'filteredTotal' => $filteredTotal,
            'overallTotal' => $overallTotal,
            'success' => Session::consumeFlash('success'),
            'error' => Session::consumeFlash('error'),
        ]);
    }

    public function create(): void
    {
        $old = Session::consumeFlash('form_old');
        $family = $this->defaultFormData();
        if (is_array($old)) {
            $family = array_merge($family, $old);
        }

        $this->renderForm('create', $family);
    }

    public function store(): void
    {
        $input = $this->sanitizeInput($_POST);

        if (!$this->validateRequired($input)) {
            Session::flash('error', 'Nome do responsavel e obrigatorio.');
            Session::flash('form_old', $input);
            Response::redirect('/families/create');
        }

        $controlledError = $this->validateControlledFields($input, null);
        if ($controlledError !== null) {
            Session::flash('error', $controlledError);
            Session::flash('form_old', $input);
            Response::redirect('/families/create');
        }

        $cpfError = $this->validateCpfAndDuplicate($input, null);
        if ($cpfError !== null) {
            Session::flash('error', $cpfError);
            Session::flash('form_old', $input);
            Response::redirect('/families/create');
        }

        try {
            $this->familyModel()->create($this->toPersistenceData($input));
        } catch (Throwable $exception) {
            Session::flash('error', 'Falha ao salvar familia.');
            Session::flash('form_old', $input);
            Response::redirect('/families/create');
        }

        Session::flash('success', 'Familia cadastrada com sucesso.');
        Response::redirect('/families');
    }

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            Session::flash('error', 'Familia invalida.');
            Response::redirect('/families');
        }

        try {
            $family = $this->familyModel()->findById($id);
        } catch (Throwable $exception) {
            Session::flash('error', 'Falha ao carregar familia.');
            Response::redirect('/families');
        }

        if ($family === null) {
            Session::flash('error', 'Familia nao encontrada.');
            Response::redirect('/families');
        }

        $old = Session::consumeFlash('form_old');
        if (is_array($old)) {
            $family = array_merge($family, $old);
        }

        $this->renderForm('edit', $family);
    }

    public function update(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            Session::flash('error', 'Familia invalida.');
            Response::redirect('/families');
        }

        $input = $this->sanitizeInput($_POST);

        if (!$this->validateRequired($input)) {
            Session::flash('error', 'Nome do responsavel e obrigatorio.');
            Session::flash('form_old', $input);
            Response::redirect('/families/edit?id=' . $id);
        }

        try {
            $existingFamily = $this->familyModel()->findById($id);
        } catch (Throwable $exception) {
            Session::flash('error', 'Falha ao carregar familia.');
            Response::redirect('/families');
        }

        if ($existingFamily === null) {
            Session::flash('error', 'Familia nao encontrada.');
            Response::redirect('/families');
        }

        $controlledError = $this->validateControlledFields($input, $existingFamily);
        if ($controlledError !== null) {
            Session::flash('error', $controlledError);
            Session::flash('form_old', $input);
            Response::redirect('/families/edit?id=' . $id);
        }

        $cpfError = $this->validateCpfAndDuplicate($input, $id);
        if ($cpfError !== null) {
            Session::flash('error', $cpfError);
            Session::flash('form_old', $input);
            Response::redirect('/families/edit?id=' . $id);
        }

        try {
            $this->familyModel()->update($id, $this->toPersistenceData($input));
        } catch (Throwable $exception) {
            Session::flash('error', 'Falha ao atualizar familia.');
            Session::flash('form_old', $input);
            Response::redirect('/families/edit?id=' . $id);
        }

        Session::flash('success', 'Familia atualizada com sucesso.');
        Response::redirect('/families');
    }

    public function delete(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            Session::flash('error', 'Familia invalida.');
            Response::redirect('/families');
        }

        try {
            if ($this->familyModel()->findById($id) === null) {
                Session::flash('error', 'Familia nao encontrada.');
                Response::redirect('/families');
            }
            $this->familyModel()->delete($id);
        } catch (Throwable $exception) {
            Session::flash('error', 'Falha ao remover familia.');
            Response::redirect('/families');
        }

        Session::flash('success', 'Familia removida com sucesso.');
        Response::redirect('/families');
    }

    public function show(): void
    {
        $familyId = (int) ($_GET['id'] ?? 0);
        if ($familyId <= 0) {
            Session::flash('error', 'Familia invalida.');
            Response::redirect('/families');
        }

        try {
            $family = $this->familyModel()->findById($familyId);
            if ($family === null) {
                Session::flash('error', 'Familia nao encontrada.');
                Response::redirect('/families');
            }

            $members = $this->familyModel()->getMembersByFamilyId($familyId);
            $children = $this->childModel()->findByFamilyId($familyId);
        } catch (Throwable $exception) {
            Session::flash('error', 'Falha ao carregar detalhes da familia.');
            Response::redirect('/families');
        }

        $memberEditId = (int) ($_GET['member_edit'] ?? 0);
        $memberEdit = null;
        if ($memberEditId > 0) {
            foreach ($members as $member) {
                if ((int) ($member['id'] ?? 0) === $memberEditId) {
                    $memberEdit = $member;
                    break;
                }
            }
        }

        $memberOld = Session::consumeFlash('member_form_old');
        if (is_array($memberOld)) {
            $memberEdit = array_merge($this->defaultMemberFormData($familyId), $memberOld);
        }

        View::render('families.show', [
            '_layout' => 'layouts.app',
            'appName' => (string) ($this->container->get('config')['app']['name'] ?? 'Dashboard PHP PBT'),
            'pageTitle' => 'Detalhe da familia',
            'activeMenu' => 'familias',
            'authUser' => Session::get('auth_user', []),
            'family' => $family,
            'members' => $members,
            'children' => $children,
            'memberForm' => $memberEdit ?? $this->defaultMemberFormData($familyId),
            'memberEditMode' => $memberEdit !== null && isset($memberEdit['id']),
            'success' => Session::consumeFlash('success'),
            'error' => Session::consumeFlash('error'),
        ]);
    }

    public function storeMember(): void
    {
        $familyId = (int) ($_GET['family_id'] ?? 0);
        if ($familyId <= 0) {
            Session::flash('error', 'Familia invalida.');
            Response::redirect('/families');
        }

        $input = $this->sanitizeMemberInput($_POST, $familyId);
        $error = $this->validateMemberInput($input);
        if ($error !== null) {
            Session::flash('error', $error);
            Session::flash('member_form_old', $input);
            Response::redirect('/families/show?id=' . $familyId);
        }

        try {
            if ($this->familyModel()->findById($familyId) === null) {
                Session::flash('error', 'Familia nao encontrada.');
                Response::redirect('/families');
            }

            $this->familyModel()->createMember($this->toMemberPersistenceData($input));
            $this->familyModel()->recalculateFamilyIndicators($familyId);
        } catch (Throwable $exception) {
            Session::flash('error', 'Falha ao adicionar membro.');
            Session::flash('member_form_old', $input);
            Response::redirect('/families/show?id=' . $familyId);
        }

        Session::flash('success', 'Membro adicionado com sucesso.');
        Response::redirect('/families/show?id=' . $familyId);
    }

    public function updateMember(): void
    {
        $familyId = (int) ($_GET['family_id'] ?? 0);
        $memberId = (int) ($_GET['id'] ?? 0);
        if ($familyId <= 0 || $memberId <= 0) {
            Session::flash('error', 'Membro invalido.');
            Response::redirect('/families');
        }

        $input = $this->sanitizeMemberInput($_POST, $familyId);
        $error = $this->validateMemberInput($input);
        if ($error !== null) {
            Session::flash('error', $error);
            $input['id'] = $memberId;
            Session::flash('member_form_old', $input);
            Response::redirect('/families/show?id=' . $familyId . '&member_edit=' . $memberId);
        }

        try {
            $member = $this->familyModel()->findMemberById($memberId);
            if ($member === null || (int) ($member['family_id'] ?? 0) !== $familyId) {
                Session::flash('error', 'Membro nao encontrado.');
                Response::redirect('/families/show?id=' . $familyId);
            }

            $this->familyModel()->updateMember($memberId, $this->toMemberPersistenceData($input));
            $this->familyModel()->recalculateFamilyIndicators($familyId);
        } catch (Throwable $exception) {
            Session::flash('error', 'Falha ao atualizar membro.');
            $input['id'] = $memberId;
            Session::flash('member_form_old', $input);
            Response::redirect('/families/show?id=' . $familyId . '&member_edit=' . $memberId);
        }

        Session::flash('success', 'Membro atualizado com sucesso.');
        Response::redirect('/families/show?id=' . $familyId);
    }

    public function deleteMember(): void
    {
        $familyId = (int) ($_GET['family_id'] ?? 0);
        $memberId = (int) ($_GET['id'] ?? 0);
        if ($familyId <= 0 || $memberId <= 0) {
            Session::flash('error', 'Membro invalido.');
            Response::redirect('/families');
        }

        try {
            $this->familyModel()->deleteMember($memberId, $familyId);
            $this->familyModel()->recalculateFamilyIndicators($familyId);
        } catch (Throwable $exception) {
            Session::flash('error', 'Falha ao remover membro.');
            Response::redirect('/families/show?id=' . $familyId);
        }

        Session::flash('success', 'Membro removido com sucesso.');
        Response::redirect('/families/show?id=' . $familyId);
    }

    private function renderForm(string $mode, array $family): void
    {
        View::render('families.form', [
            '_layout' => 'layouts.app',
            'appName' => (string) ($this->container->get('config')['app']['name'] ?? 'Dashboard PHP PBT'),
            'pageTitle' => $mode === 'edit' ? 'Editar familia' : 'Nova familia',
            'activeMenu' => 'familias',
            'authUser' => Session::get('auth_user', []),
            'mode' => $mode,
            'family' => $family,
            'docStatuses' => self::DOC_STATUSES,
            'housingTypes' => $this->withLegacyOption(self::HOUSING_TYPES, (string) ($family['housing_type'] ?? '')),
            'maritalStatuses' => $this->withLegacyOption(self::MARITAL_STATUSES, (string) ($family['marital_status'] ?? '')),
            'educationLevels' => $this->withLegacyOption(self::EDUCATION_LEVELS, (string) ($family['education_level'] ?? '')),
            'professionalStatuses' => $this->withLegacyOption(self::PROFESSIONAL_STATUSES, (string) ($family['professional_status'] ?? '')),
            'error' => Session::consumeFlash('error'),
        ]);
    }

    private function familyModel(): FamilyModel
    {
        /** @var PDO $pdo */
        $pdo = $this->container->get('db');
        return new FamilyModel($pdo);
    }

    private function childModel(): ChildModel
    {
        /** @var PDO $pdo */
        $pdo = $this->container->get('db');
        return new ChildModel($pdo);
    }

    private function defaultFormData(): array
    {
        return [
            'responsible_name' => '',
            'cpf_responsible' => '',
            'rg_responsible' => '',
            'birth_date' => '',
            'phone' => '',
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
            'children_count' => 0,
            'documentation_status' => 'ok',
            'documentation_notes' => '',
            'needs_visit' => 0,
            'general_notes' => '',
            'is_active' => 1,
        ];
    }

    private function sanitizeInput(array $post): array
    {
        return [
            'responsible_name' => trim((string) ($post['responsible_name'] ?? '')),
            'cpf_responsible' => trim((string) ($post['cpf_responsible'] ?? '')),
            'rg_responsible' => $this->sanitizeRg((string) ($post['rg_responsible'] ?? '')),
            'birth_date' => trim((string) ($post['birth_date'] ?? '')),
            'phone' => $this->sanitizePhone((string) ($post['phone'] ?? '')),
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
            'adults_count' => max(0, (int) ($post['adults_count'] ?? 0)),
            'workers_count' => max(0, (int) ($post['workers_count'] ?? 0)),
            'family_income_total' => $this->sanitizeMoney((string) ($post['family_income_total'] ?? '0')),
            'children_count' => max(0, (int) ($post['children_count'] ?? 0)),
            'documentation_status' => trim((string) ($post['documentation_status'] ?? 'ok')),
            'documentation_notes' => trim((string) ($post['documentation_notes'] ?? '')),
            'needs_visit' => isset($post['needs_visit']) ? 1 : 0,
            'general_notes' => trim((string) ($post['general_notes'] ?? '')),
            'is_active' => isset($post['is_active']) ? 1 : 0,
        ];
    }

    private function sanitizeMoney(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '0.00';
        }

        $value = preg_replace('/[^\d,.\-]/', '', $value);
        if (!is_string($value) || $value === '') {
            return '0.00';
        }

        if (str_contains($value, ',')) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        }

        $filtered = preg_replace('/[^0-9.\-]/', '', $value);
        if (!is_string($filtered) || $filtered === '') {
            return '0.00';
        }

        return number_format((float) $filtered, 2, '.', '');
    }

    private function validateRequired(array $input): bool
    {
        return trim((string) ($input['responsible_name'] ?? '')) !== '';
    }

    private function validateControlledFields(array $input, ?array $existing): ?string
    {
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

            $legacyValue = trim((string) ($existing[$field] ?? ''));
            if ($legacyValue !== '' && $legacyValue === $value) {
                continue;
            }

            return (string) ($config['label'] ?? 'Campo') . ' invalido.';
        }

        return null;
    }

    private function validateCpfAndDuplicate(array &$input, ?int $excludeId): ?string
    {
        $cpfRaw = (string) ($input['cpf_responsible'] ?? '');
        if ($cpfRaw === '') {
            $input['cpf_responsible'] = '';
            return null;
        }

        if (!CpfService::isValid($cpfRaw)) {
            return 'CPF invalido.';
        }

        $cpfFormatted = (string) CpfService::format($cpfRaw);
        $input['cpf_responsible'] = $cpfFormatted;

        try {
            $duplicate = $this->familyModel()->findByCpfExcludingId($cpfFormatted, $excludeId);
        } catch (Throwable $exception) {
            return 'Falha ao validar duplicidade de CPF.';
        }

        return $duplicate !== null ? 'Ja existe familia cadastrada com este CPF.' : null;
    }

    private function sanitizeRg(string $value): string
    {
        $raw = preg_replace('/[^0-9A-Z]/', '', strtoupper(trim($value)));
        if (!is_string($raw) || $raw === '') {
            return '';
        }

        $raw = substr($raw, 0, 9);
        if (strlen($raw) <= 2) {
            return $raw;
        }
        if (strlen($raw) <= 5) {
            return substr($raw, 0, 2) . '.' . substr($raw, 2);
        }
        if (strlen($raw) <= 8) {
            return substr($raw, 0, 2) . '.' . substr($raw, 2, 3) . '.' . substr($raw, 5);
        }

        return substr($raw, 0, 2) . '.' . substr($raw, 2, 3) . '.' . substr($raw, 5, 3) . '-' . substr($raw, 8, 1);
    }

    private function sanitizePhone(string $value): string
    {
        $digits = preg_replace('/\D+/', '', trim($value));
        if (!is_string($digits) || $digits === '') {
            return '';
        }

        $digits = substr($digits, 0, 11);
        if (strlen($digits) <= 2) {
            return $digits;
        }

        if (strlen($digits) <= 10) {
            if (strlen($digits) <= 6) {
                return '(' . substr($digits, 0, 2) . ') ' . substr($digits, 2);
            }

            return '(' . substr($digits, 0, 2) . ') ' . substr($digits, 2, 4) . '-' . substr($digits, 6);
        }

        return '(' . substr($digits, 0, 2) . ') ' . substr($digits, 2, 5) . '-' . substr($digits, 7);
    }

    private function defaultMemberFormData(int $familyId): array
    {
        return [
            'family_id' => $familyId,
            'name' => '',
            'relationship' => '',
            'birth_date' => '',
            'works' => 0,
            'income' => '0.00',
        ];
    }

    private function sanitizeMemberInput(array $post, int $familyId): array
    {
        return [
            'family_id' => $familyId,
            'name' => trim((string) ($post['name'] ?? '')),
            'relationship' => trim((string) ($post['relationship'] ?? '')),
            'birth_date' => trim((string) ($post['birth_date'] ?? '')),
            'works' => isset($post['works']) ? 1 : 0,
            'income' => $this->sanitizeMoney((string) ($post['income'] ?? '0')),
        ];
    }

    private function validateMemberInput(array $input): ?string
    {
        if (trim((string) ($input['name'] ?? '')) === '') {
            return 'Nome do membro e obrigatorio.';
        }

        if (!is_numeric((string) ($input['income'] ?? '0'))) {
            return 'Renda do membro invalida.';
        }

        return null;
    }

    private function toMemberPersistenceData(array $input): array
    {
        return [
            'family_id' => (int) $input['family_id'],
            'name' => $input['name'],
            'relationship' => $input['relationship'] !== '' ? $input['relationship'] : null,
            'birth_date' => $input['birth_date'] !== '' ? $input['birth_date'] : null,
            'works' => (int) $input['works'],
            'income' => $input['income'],
        ];
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
            'adults_count' => (int) $input['adults_count'],
            'workers_count' => (int) $input['workers_count'],
            'family_income_total' => $input['family_income_total'],
            'children_count' => (int) $input['children_count'],
            'documentation_status' => $input['documentation_status'],
            'documentation_notes' => $input['documentation_notes'] !== '' ? $input['documentation_notes'] : null,
            'needs_visit' => (int) $input['needs_visit'],
            'general_notes' => $input['general_notes'] !== '' ? $input['general_notes'] : null,
            'is_active' => (int) $input['is_active'],
        ];
    }

    private function withLegacyOption(array $baseOptions, string $selectedValue): array
    {
        if ($selectedValue === '' || isset($baseOptions[$selectedValue])) {
            return $baseOptions;
        }

        $baseOptions[$selectedValue] = 'Legado: ' . $selectedValue;
        return $baseOptions;
    }
}
