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
    private const PERSON_TYPES = ['principal', 'member', 'dependent', 'child'];

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

        $requiredError = $this->validateRequired($input);
        if ($requiredError !== null) {
            Session::flash('error', $requiredError);
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
            $familyId = $this->familyModel()->create($this->toPersistenceData($input));
            $this->familyModel()->recalculateFamilyIndicators($familyId);
        } catch (Throwable $exception) {
            Session::flash('error', 'Falha ao salvar familia.');
            Session::flash('form_old', $input);
            Response::redirect('/families/create');
        }

        Session::flash('success', 'Familia cadastrada com sucesso. Continue no cadastro de pessoas da familia.');
        Response::redirect('/families/show?id=' . $familyId . '&person_type=principal');
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

        $requiredError = $this->validateRequired($input);
        if ($requiredError !== null) {
            Session::flash('error', $requiredError);
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
            $this->familyModel()->recalculateFamilyIndicators($id);
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

        $requestedPersonType = $this->sanitizePersonType((string) ($_GET['person_type'] ?? ''));
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
        $hasMemberOld = is_array($memberOld);
        if ($hasMemberOld) {
            $memberEdit = array_merge($this->defaultMemberFormData($familyId), $memberOld);
        }

        $childEditId = (int) ($_GET['child_edit'] ?? 0);
        $childEdit = null;
        if ($childEditId > 0) {
            foreach ($children as $child) {
                if ((int) ($child['id'] ?? 0) === $childEditId) {
                    $childEdit = $child;
                    break;
                }
            }
        }

        $childOld = Session::consumeFlash('child_form_old');
        $hasChildOld = is_array($childOld);
        if ($hasChildOld) {
            $childEdit = array_merge($this->defaultChildFormData($familyId), $childOld);
        }

        $principalOld = Session::consumeFlash('principal_form_old');
        $hasPrincipalOld = is_array($principalOld);
        $principalForm = $this->defaultPrincipalFormData($family);
        if ($hasPrincipalOld) {
            $principalForm = array_merge($principalForm, $principalOld);
        }

        $memberForm = $memberEdit ?? $this->defaultMemberFormData($familyId);
        $childForm = $childEdit ?? $this->defaultChildFormData($familyId);
        $memberEditMode = $memberEdit !== null && isset($memberEdit['id']);
        $childEditMode = $childEdit !== null && isset($childEdit['id']);

        $personType = 'member';
        if ($childEditMode || $hasChildOld) {
            $personType = 'child';
        } elseif ($memberEditMode || $hasMemberOld) {
            $personType = $this->resolveMemberPersonType($memberForm);
        } elseif ($hasPrincipalOld) {
            $personType = 'principal';
        } elseif ($requestedPersonType !== '') {
            $personType = $requestedPersonType;
        }

        $openPersonForm = $memberEditMode || $childEditMode || $hasMemberOld || $hasChildOld || $hasPrincipalOld || $requestedPersonType !== '';

        View::render('families.show', [
            '_layout' => 'layouts.app',
            'appName' => (string) ($this->container->get('config')['app']['name'] ?? 'Dashboard PHP PBT'),
            'pageTitle' => 'Detalhe da familia',
            'activeMenu' => 'familias',
            'authUser' => Session::get('auth_user', []),
            'family' => $family,
            'members' => $members,
            'children' => $children,
            'memberForm' => $memberForm,
            'memberEditMode' => $memberEditMode,
            'childForm' => $childForm,
            'childEditMode' => $childEditMode,
            'principalForm' => $principalForm,
            'personType' => $personType,
            'openPersonForm' => $openPersonForm,
            'success' => Session::consumeFlash('success'),
            'error' => Session::consumeFlash('error'),
        ]);
    }

    public function updatePrincipal(): void
    {
        $familyId = (int) ($_GET['family_id'] ?? 0);
        if ($familyId <= 0) {
            Session::flash('error', 'Familia invalida.');
            Response::redirect('/families');
        }

        try {
            $family = $this->familyModel()->findById($familyId);
        } catch (Throwable $exception) {
            Session::flash('error', 'Falha ao carregar familia.');
            Response::redirect('/families');
        }

        if ($family === null) {
            Session::flash('error', 'Familia nao encontrada.');
            Response::redirect('/families');
        }

        $input = $this->sanitizeResponsibleInput($_POST);
        $error = $this->validateResponsibleInput($input, $familyId);
        if ($error !== null) {
            Session::flash('error', $error);
            Session::flash('principal_form_old', $input);
            Response::redirect($this->familyShowUrl($familyId, 'principal'));
        }

        try {
            $this->familyModel()->updateResponsible($familyId, $this->toResponsiblePersistenceData($input));
        } catch (Throwable $exception) {
            Session::flash('error', 'Falha ao atualizar responsavel principal.');
            Session::flash('principal_form_old', $input);
            Response::redirect($this->familyShowUrl($familyId, 'principal'));
        }

        Session::flash('success', 'Responsavel principal atualizado com sucesso.');
        Response::redirect($this->familyShowUrl($familyId, 'principal'));
    }

    public function storeMember(): void
    {
        $familyId = (int) ($_GET['family_id'] ?? 0);
        if ($familyId <= 0) {
            Session::flash('error', 'Familia invalida.');
            Response::redirect('/families');
        }

        $input = $this->sanitizeMemberInput($_POST, $familyId);
        $this->applyMemberPersonTypeRules($input);
        $personType = (string) ($input['person_type'] ?? 'member');
        $error = $this->validateMemberInput($input, null);
        if ($error !== null) {
            Session::flash('error', $error);
            Session::flash('member_form_old', $input);
            Response::redirect($this->familyShowUrl($familyId, $personType));
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
            Response::redirect($this->familyShowUrl($familyId, $personType));
        }

        Session::flash('success', $personType === 'dependent' ? 'Dependente adicionado com sucesso.' : 'Membro adicionado com sucesso.');
        Response::redirect($this->familyShowUrl($familyId, $personType));
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
        $this->applyMemberPersonTypeRules($input);
        $personType = (string) ($input['person_type'] ?? 'member');
        $error = $this->validateMemberInput($input, $memberId);
        if ($error !== null) {
            Session::flash('error', $error);
            $input['id'] = $memberId;
            Session::flash('member_form_old', $input);
            Response::redirect($this->familyShowUrl($familyId, $personType, ['member_edit' => $memberId]));
        }

        try {
            $member = $this->familyModel()->findMemberById($memberId);
            if ($member === null || (int) ($member['family_id'] ?? 0) !== $familyId) {
                Session::flash('error', 'Membro nao encontrado.');
                Response::redirect($this->familyShowUrl($familyId, $personType));
            }

            $this->familyModel()->updateMember($memberId, $this->toMemberPersistenceData($input));
            $this->familyModel()->recalculateFamilyIndicators($familyId);
        } catch (Throwable $exception) {
            Session::flash('error', 'Falha ao atualizar membro.');
            $input['id'] = $memberId;
            Session::flash('member_form_old', $input);
            Response::redirect($this->familyShowUrl($familyId, $personType, ['member_edit' => $memberId]));
        }

        Session::flash('success', $personType === 'dependent' ? 'Dependente atualizado com sucesso.' : 'Membro atualizado com sucesso.');
        Response::redirect($this->familyShowUrl($familyId, $personType));
    }

    public function deleteMember(): void
    {
        $familyId = (int) ($_GET['family_id'] ?? 0);
        $memberId = (int) ($_GET['id'] ?? 0);
        $personType = $this->sanitizePersonType((string) ($_GET['person_type'] ?? ''));
        if ($familyId <= 0 || $memberId <= 0) {
            Session::flash('error', 'Membro invalido.');
            Response::redirect('/families');
        }

        try {
            if ($personType === '') {
                $member = $this->familyModel()->findMemberById($memberId);
                if ($member !== null) {
                    $personType = $this->resolveMemberPersonType($member);
                }
            }

            $this->familyModel()->deleteMember($memberId, $familyId);
            $this->familyModel()->recalculateFamilyIndicators($familyId);
        } catch (Throwable $exception) {
            Session::flash('error', 'Falha ao remover membro.');
            Response::redirect($this->familyShowUrl($familyId, $personType === '' ? 'member' : $personType));
        }

        if ($personType === '') {
            $personType = 'member';
        }

        Session::flash('success', $personType === 'dependent' ? 'Dependente removido com sucesso.' : 'Membro removido com sucesso.');
        Response::redirect($this->familyShowUrl($familyId, $personType));
    }

    public function storeChild(): void
    {
        $familyId = (int) ($_GET['family_id'] ?? 0);
        if ($familyId <= 0) {
            Session::flash('error', 'Familia invalida.');
            Response::redirect('/families');
        }

        $input = $this->sanitizeChildInput($_POST, $familyId);
        $error = $this->validateChildInput($input, null);
        if ($error !== null) {
            Session::flash('error', $error);
            Session::flash('child_form_old', $input);
            Response::redirect($this->familyShowUrl($familyId, 'child'));
        }

        try {
            if ($this->familyModel()->findById($familyId) === null) {
                Session::flash('error', 'Familia nao encontrada.');
                Response::redirect('/families');
            }

            $this->childModel()->create($this->toChildPersistenceData($input));
            $this->familyModel()->recalculateFamilyIndicators($familyId);
        } catch (Throwable $exception) {
            Session::flash('error', 'Falha ao adicionar crianca.');
            Session::flash('child_form_old', $input);
            Response::redirect($this->familyShowUrl($familyId, 'child'));
        }

        Session::flash('success', 'Crianca adicionada com sucesso.');
        Response::redirect($this->familyShowUrl($familyId, 'child'));
    }

    public function updateChild(): void
    {
        $familyId = (int) ($_GET['family_id'] ?? 0);
        $childId = (int) ($_GET['id'] ?? 0);
        if ($familyId <= 0 || $childId <= 0) {
            Session::flash('error', 'Crianca invalida.');
            Response::redirect('/families');
        }

        $input = $this->sanitizeChildInput($_POST, $familyId);
        $error = $this->validateChildInput($input, $childId);
        if ($error !== null) {
            Session::flash('error', $error);
            $input['id'] = $childId;
            Session::flash('child_form_old', $input);
            Response::redirect($this->familyShowUrl($familyId, 'child', ['child_edit' => $childId]));
        }

        try {
            $child = $this->childModel()->findById($childId);
            if ($child === null || (int) ($child['family_id'] ?? 0) !== $familyId) {
                Session::flash('error', 'Crianca nao encontrada.');
                Response::redirect($this->familyShowUrl($familyId, 'child'));
            }

            $this->childModel()->update($childId, $this->toChildPersistenceData($input));
            $this->familyModel()->recalculateFamilyIndicators($familyId);
        } catch (Throwable $exception) {
            Session::flash('error', 'Falha ao atualizar crianca.');
            $input['id'] = $childId;
            Session::flash('child_form_old', $input);
            Response::redirect($this->familyShowUrl($familyId, 'child', ['child_edit' => $childId]));
        }

        Session::flash('success', 'Crianca atualizada com sucesso.');
        Response::redirect($this->familyShowUrl($familyId, 'child'));
    }

    public function deleteChild(): void
    {
        $familyId = (int) ($_GET['family_id'] ?? 0);
        $childId = (int) ($_GET['id'] ?? 0);
        if ($familyId <= 0 || $childId <= 0) {
            Session::flash('error', 'Crianca invalida.');
            Response::redirect('/families');
        }

        try {
            $child = $this->childModel()->findById($childId);
            if ($child === null || (int) ($child['family_id'] ?? 0) !== $familyId) {
                Session::flash('error', 'Crianca nao encontrada.');
                Response::redirect($this->familyShowUrl($familyId, 'child'));
            }

            $this->childModel()->delete($childId);
            $this->familyModel()->recalculateFamilyIndicators($familyId);
        } catch (Throwable $exception) {
            Session::flash('error', 'Falha ao remover crianca.');
            Response::redirect($this->familyShowUrl($familyId, 'child'));
        }

        Session::flash('success', 'Crianca removida com sucesso.');
        Response::redirect($this->familyShowUrl($familyId, 'child'));
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

    private function validateRequired(array $input): ?string
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

        if (!$this->isRgValid((string) ($input['rg_responsible'] ?? ''))) {
            return 'RG invalido. Use o formato 00.000.000-0.';
        }

        return null;
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
            return 'CPF do responsavel principal e obrigatorio.';
        }

        if (!CpfService::isValid($cpfRaw)) {
            return 'CPF invalido.';
        }

        $cpfFormatted = (string) CpfService::format($cpfRaw);
        $input['cpf_responsible'] = $cpfFormatted;

        try {
            $conflict = $this->familyModel()->findCpfConflict($cpfFormatted, [
                'family_id' => $excludeId ?? 0,
            ]);
        } catch (Throwable $exception) {
            return 'Falha ao validar duplicidade de CPF.';
        }

        if ($conflict !== null) {
            return $this->buildCpfConflictMessage($conflict);
        }

        return null;
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
            'cpf' => '',
            'rg' => '',
            'birth_date' => '',
            'works' => 0,
            'income' => '0.00',
            'person_type' => 'member',
        ];
    }

    private function defaultChildFormData(int $familyId): array
    {
        return [
            'family_id' => $familyId,
            'name' => '',
            'cpf' => '',
            'rg' => '',
            'birth_date' => '',
            'age_years' => '',
            'relationship' => '',
            'notes' => '',
            'person_type' => 'child',
        ];
    }

    private function defaultPrincipalFormData(array $family): array
    {
        return [
            'responsible_name' => (string) ($family['responsible_name'] ?? ''),
            'cpf_responsible' => (string) ($family['cpf_responsible'] ?? ''),
            'rg_responsible' => (string) ($family['rg_responsible'] ?? ''),
            'birth_date' => (string) ($family['birth_date'] ?? ''),
            'phone' => (string) ($family['phone'] ?? ''),
            'person_type' => 'principal',
        ];
    }

    private function sanitizeMemberInput(array $post, int $familyId): array
    {
        return [
            'family_id' => $familyId,
            'name' => trim((string) ($post['name'] ?? '')),
            'relationship' => trim((string) ($post['relationship'] ?? '')),
            'cpf' => trim((string) ($post['cpf'] ?? '')),
            'rg' => $this->sanitizeRg((string) ($post['rg'] ?? '')),
            'birth_date' => trim((string) ($post['birth_date'] ?? '')),
            'works' => isset($post['works']) ? 1 : 0,
            'income' => $this->sanitizeMoney((string) ($post['income'] ?? '0')),
            'person_type' => $this->sanitizePersonType((string) ($post['person_type'] ?? ''), 'member'),
        ];
    }

    private function sanitizeChildInput(array $post, int $familyId): array
    {
        $age = trim((string) ($post['age_years'] ?? ''));
        return [
            'family_id' => $familyId,
            'name' => trim((string) ($post['name'] ?? '')),
            'cpf' => trim((string) ($post['cpf'] ?? '')),
            'rg' => $this->sanitizeRg((string) ($post['rg'] ?? '')),
            'birth_date' => trim((string) ($post['birth_date'] ?? '')),
            'age_years' => $age === '' ? null : max(0, (int) $age),
            'relationship' => trim((string) ($post['relationship'] ?? '')),
            'notes' => trim((string) ($post['notes'] ?? '')),
            'person_type' => 'child',
        ];
    }

    private function sanitizeResponsibleInput(array $post): array
    {
        return [
            'responsible_name' => trim((string) ($post['responsible_name'] ?? '')),
            'cpf_responsible' => trim((string) ($post['cpf_responsible'] ?? '')),
            'rg_responsible' => $this->sanitizeRg((string) ($post['rg_responsible'] ?? '')),
            'birth_date' => trim((string) ($post['birth_date'] ?? '')),
            'phone' => $this->sanitizePhone((string) ($post['phone'] ?? '')),
            'person_type' => 'principal',
        ];
    }

    private function validateMemberInput(array &$input, ?int $memberId): ?string
    {
        if (trim((string) ($input['name'] ?? '')) === '') {
            return 'Nome do membro e obrigatorio.';
        }

        if (trim((string) ($input['cpf'] ?? '')) === '') {
            return 'CPF do membro/dependente e obrigatorio.';
        }

        if (trim((string) ($input['rg'] ?? '')) === '') {
            return 'RG do membro/dependente e obrigatorio.';
        }

        if (!$this->isRgValid((string) ($input['rg'] ?? ''))) {
            return 'RG invalido. Use o formato 00.000.000-0.';
        }

        if (!CpfService::isValid((string) ($input['cpf'] ?? ''))) {
            return 'CPF invalido.';
        }

        $input['cpf'] = (string) CpfService::format((string) $input['cpf']);

        try {
            $conflict = $this->familyModel()->findCpfConflict((string) $input['cpf'], [
                'member_id' => $memberId ?? 0,
            ]);
        } catch (Throwable $exception) {
            return 'Falha ao validar duplicidade de CPF.';
        }

        if ($conflict !== null) {
            return $this->buildCpfConflictMessage($conflict);
        }

        if (!is_numeric((string) ($input['income'] ?? '0'))) {
            return 'Renda do membro invalida.';
        }

        return null;
    }

    private function validateChildInput(array &$input, ?int $childId): ?string
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
                $conflict = $this->familyModel()->findCpfConflict((string) $input['cpf'], [
                    'child_id' => $childId ?? 0,
                ]);
            } catch (Throwable $exception) {
                return 'Falha ao validar duplicidade de CPF.';
            }

            if ($conflict !== null) {
                return $this->buildCpfConflictMessage($conflict);
            }
        }

        $rg = trim((string) ($input['rg'] ?? ''));
        if ($rg !== '' && !$this->isRgValid($rg)) {
            return 'RG invalido. Use o formato 00.000.000-0.';
        }

        return null;
    }

    private function validateResponsibleInput(array &$input, int $familyId): ?string
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

        if (!$this->isRgValid((string) ($input['rg_responsible'] ?? ''))) {
            return 'RG invalido. Use o formato 00.000.000-0.';
        }

        if (!CpfService::isValid((string) ($input['cpf_responsible'] ?? ''))) {
            return 'CPF invalido.';
        }

        $input['cpf_responsible'] = (string) CpfService::format((string) $input['cpf_responsible']);
        try {
            $conflict = $this->familyModel()->findCpfConflict((string) $input['cpf_responsible'], [
                'family_id' => $familyId,
            ]);
        } catch (Throwable $exception) {
            return 'Falha ao validar duplicidade de CPF.';
        }

        if ($conflict !== null) {
            return $this->buildCpfConflictMessage($conflict);
        }

        return null;
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
            'works' => (int) $input['works'],
            'income' => $input['income'],
        ];
    }

    private function toChildPersistenceData(array $input): array
    {
        return [
            'family_id' => (int) $input['family_id'],
            'name' => $input['name'],
            'cpf' => ($input['cpf'] ?? '') !== '' ? $input['cpf'] : null,
            'rg' => ($input['rg'] ?? '') !== '' ? $input['rg'] : null,
            'birth_date' => ($input['birth_date'] ?? '') !== '' ? $input['birth_date'] : null,
            'age_years' => $input['age_years'],
            'relationship' => ($input['relationship'] ?? '') !== '' ? $input['relationship'] : null,
            'notes' => ($input['notes'] ?? '') !== '' ? $input['notes'] : null,
        ];
    }

    private function isRgValid(string $rg): bool
    {
        return preg_match('/^\d{2}\.\d{3}\.\d{3}-[0-9A-Z]$/', trim($rg)) === 1;
    }

    private function buildCpfConflictMessage(array $conflict): string
    {
        $source = (string) ($conflict['source_table'] ?? '');
        $name = trim((string) ($conflict['source_name'] ?? ''));

        $target = match ($source) {
            'families' => 'familia',
            'family_members' => 'membro/dependente',
            'children' => 'crianca',
            'people' => 'pessoa',
            default => 'cadastro existente',
        };

        if ($name !== '') {
            return 'CPF ja cadastrado em ' . $target . ': ' . $name . '.';
        }

        return 'CPF ja cadastrado no sistema.';
    }

    private function toResponsiblePersistenceData(array $input): array
    {
        return [
            'responsible_name' => $input['responsible_name'],
            'cpf_responsible' => $input['cpf_responsible'] !== '' ? $input['cpf_responsible'] : null,
            'rg_responsible' => $input['rg_responsible'] !== '' ? $input['rg_responsible'] : null,
            'birth_date' => $input['birth_date'] !== '' ? $input['birth_date'] : null,
            'phone' => $input['phone'] !== '' ? $input['phone'] : null,
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

    private function sanitizePersonType(string $value, string $default = ''): string
    {
        $value = strtolower(trim($value));
        if (in_array($value, self::PERSON_TYPES, true)) {
            return $value;
        }

        return $default;
    }

    private function resolveMemberPersonType(array $input): string
    {
        $personType = $this->sanitizePersonType((string) ($input['person_type'] ?? ''));
        if ($personType === 'dependent') {
            return 'dependent';
        }

        if ($this->isDependentRelationship((string) ($input['relationship'] ?? ''))) {
            return 'dependent';
        }

        return 'member';
    }

    private function applyMemberPersonTypeRules(array &$input): void
    {
        $personType = $this->sanitizePersonType((string) ($input['person_type'] ?? ''), 'member');
        if ($personType !== 'dependent') {
            $personType = 'member';
        }

        $input['person_type'] = $personType;
        if ($personType === 'dependent') {
            $input['relationship'] = 'Dependente';
        }
    }

    private function isDependentRelationship(string $relationship): bool
    {
        return strtolower(trim($relationship)) === 'dependente';
    }

    private function familyShowUrl(int $familyId, string $personType = '', array $extra = []): string
    {
        $query = ['id' => $familyId];
        $sanitizedPersonType = $this->sanitizePersonType($personType);
        if ($sanitizedPersonType !== '') {
            $query['person_type'] = $sanitizedPersonType;
        }

        foreach ($extra as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $query[$key] = $value;
        }

        return '/families/show?' . http_build_query($query);
    }
}
