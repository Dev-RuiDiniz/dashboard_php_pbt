<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Container;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\ChildModel;
use App\Models\DeliveryModel;
use App\Models\EquipmentLoanModel;
use App\Models\FamilyModel;
use App\Models\VisitModel;
use App\Services\FamilyCompositionService;
use App\Services\FamilyDetailService;
use App\Services\FamilyIndicatorsService;
use App\Services\FamilyRegistrationService;
use PDO;
use Throwable;

final class FamilyController
{
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
        } catch (Throwable) {
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
        $family = $this->registrationService()->defaultFormData();
        $old = Session::consumeFlash('form_old');
        if (is_array($old)) {
            $family = array_merge($family, $old);
        }

        $this->renderForm('create', $family);
    }

    public function store(): void
    {
        $input = $this->registrationService()->sanitize($_POST);
        $error = $this->registrationService()->validate($input, null, null);
        if ($error !== null) {
            Session::flash('error', $error);
            Session::flash('form_old', $input);
            Response::redirect('/families/create');
        }

        try {
            $familyId = $this->registrationService()->create($input);
        } catch (Throwable) {
            Session::flash('error', 'Falha ao salvar familia.');
            Session::flash('form_old', $input);
            Response::redirect('/families/create');
        }

        Session::flash('success', 'Familia cadastrada com sucesso. Continue no cadastro de pessoas da familia.');
        Response::redirect($this->familyShowUrl($familyId, 'composition', 'principal'));
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
        } catch (Throwable) {
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

        $input = $this->registrationService()->sanitize($_POST);

        try {
            $existingFamily = $this->familyModel()->findById($id);
        } catch (Throwable) {
            Session::flash('error', 'Falha ao carregar familia.');
            Response::redirect('/families');
        }

        if ($existingFamily === null) {
            Session::flash('error', 'Familia nao encontrada.');
            Response::redirect('/families');
        }

        $error = $this->registrationService()->validate($input, $existingFamily, $id);
        if ($error !== null) {
            Session::flash('error', $error);
            Session::flash('form_old', $input);
            Response::redirect('/families/edit?id=' . $id);
        }

        try {
            $this->registrationService()->update($id, $input);
        } catch (Throwable) {
            Session::flash('error', 'Falha ao atualizar familia.');
            Session::flash('form_old', $input);
            Response::redirect('/families/edit?id=' . $id);
        }

        Session::flash('success', 'Familia atualizada com sucesso.');
        Response::redirect($this->familyShowUrl($id, 'summary'));
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
        } catch (Throwable) {
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
            $detail = $this->detailService()->build($familyId, $_GET, [
                'member_form_old' => Session::consumeFlash('member_form_old'),
                'child_form_old' => Session::consumeFlash('child_form_old'),
                'principal_form_old' => Session::consumeFlash('principal_form_old'),
            ]);
        } catch (Throwable) {
            Session::flash('error', 'Falha ao carregar detalhes da familia.');
            Response::redirect('/families');
        }

        if (!is_array($detail['family'] ?? null)) {
            Session::flash('error', 'Familia nao encontrada.');
            Response::redirect('/families');
        }

        View::render('families.show', [
            '_layout' => 'layouts.app',
            'appName' => (string) ($this->container->get('config')['app']['name'] ?? 'Dashboard PHP PBT'),
            'pageTitle' => 'Detalhe da familia',
            'activeMenu' => 'familias',
            'authUser' => Session::get('auth_user', []),
            'success' => Session::consumeFlash('success'),
            'error' => Session::consumeFlash('error'),
            ...$detail,
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
        } catch (Throwable) {
            Session::flash('error', 'Falha ao carregar familia.');
            Response::redirect('/families');
        }

        if ($family === null) {
            Session::flash('error', 'Familia nao encontrada.');
            Response::redirect('/families');
        }

        $input = $this->compositionService()->sanitizePrincipalInput($_POST);
        $error = $this->compositionService()->validatePrincipalInput($input, $familyId);
        if ($error !== null) {
            Session::flash('error', $error);
            Session::flash('principal_form_old', $input);
            Response::redirect($this->familyShowUrl($familyId, 'composition', 'principal'));
        }

        try {
            $this->compositionService()->updatePrincipal($familyId, $input);
        } catch (Throwable) {
            Session::flash('error', 'Falha ao atualizar responsavel principal.');
            Session::flash('principal_form_old', $input);
            Response::redirect($this->familyShowUrl($familyId, 'composition', 'principal'));
        }

        Session::flash('success', 'Responsavel principal atualizado com sucesso.');
        Response::redirect($this->familyShowUrl($familyId, 'composition', 'principal'));
    }

    public function storeMember(): void
    {
        $familyId = (int) ($_GET['family_id'] ?? 0);
        if ($familyId <= 0) {
            Session::flash('error', 'Familia invalida.');
            Response::redirect('/families');
        }

        $input = $this->compositionService()->sanitizeMemberInput($_POST, $familyId);
        $personType = (string) ($input['person_type'] ?? 'member');
        $error = $this->compositionService()->validateMemberInput($input, null);
        if ($error !== null) {
            Session::flash('error', $error);
            Session::flash('member_form_old', $input);
            Response::redirect($this->familyShowUrl($familyId, 'composition', $personType));
        }

        try {
            if ($this->familyModel()->findById($familyId) === null) {
                Session::flash('error', 'Familia nao encontrada.');
                Response::redirect('/families');
            }

            $this->compositionService()->createMember($input);
        } catch (Throwable) {
            Session::flash('error', 'Falha ao adicionar membro.');
            Session::flash('member_form_old', $input);
            Response::redirect($this->familyShowUrl($familyId, 'composition', $personType));
        }

        Session::flash('success', 'Membro adicionado com sucesso.');
        Response::redirect($this->familyShowUrl($familyId, 'composition', $personType));
    }

    public function updateMember(): void
    {
        $familyId = (int) ($_GET['family_id'] ?? 0);
        $memberId = (int) ($_GET['id'] ?? 0);
        if ($familyId <= 0 || $memberId <= 0) {
            Session::flash('error', 'Membro invalido.');
            Response::redirect('/families');
        }

        $input = $this->compositionService()->sanitizeMemberInput($_POST, $familyId);
        $personType = (string) ($input['person_type'] ?? 'member');
        $error = $this->compositionService()->validateMemberInput($input, $memberId);
        if ($error !== null) {
            Session::flash('error', $error);
            $input['id'] = $memberId;
            Session::flash('member_form_old', $input);
            Response::redirect($this->familyShowUrl($familyId, 'composition', $personType, ['member_edit' => $memberId]));
        }

        try {
            $member = $this->familyModel()->findMemberById($memberId);
            if ($member === null || (int) ($member['family_id'] ?? 0) !== $familyId) {
                Session::flash('error', 'Membro nao encontrado.');
                Response::redirect($this->familyShowUrl($familyId, 'composition', $personType));
            }

            $this->compositionService()->updateMember($memberId, $input);
        } catch (Throwable) {
            Session::flash('error', 'Falha ao atualizar membro.');
            $input['id'] = $memberId;
            Session::flash('member_form_old', $input);
            Response::redirect($this->familyShowUrl($familyId, 'composition', $personType, ['member_edit' => $memberId]));
        }

        Session::flash('success', 'Membro atualizado com sucesso.');
        Response::redirect($this->familyShowUrl($familyId, 'composition', $personType));
    }

    public function deleteMember(): void
    {
        $familyId = (int) ($_GET['family_id'] ?? 0);
        $memberId = (int) ($_GET['id'] ?? 0);
        $personType = $this->compositionService()->sanitizePersonType((string) ($_GET['person_type'] ?? ''), 'member');
        if ($familyId <= 0 || $memberId <= 0) {
            Session::flash('error', 'Membro invalido.');
            Response::redirect('/families');
        }

        try {
            $this->compositionService()->deleteMember($memberId, $familyId);
        } catch (Throwable) {
            Session::flash('error', 'Falha ao remover membro.');
            Response::redirect($this->familyShowUrl($familyId, 'composition', $personType));
        }

        Session::flash('success', 'Membro removido com sucesso.');
        Response::redirect($this->familyShowUrl($familyId, 'composition', $personType));
    }

    public function storeChild(): void
    {
        $familyId = (int) ($_GET['family_id'] ?? 0);
        if ($familyId <= 0) {
            Session::flash('error', 'Familia invalida.');
            Response::redirect('/families');
        }

        $input = $this->compositionService()->sanitizeChildInput($_POST, $familyId);
        $error = $this->compositionService()->validateChildInput($input, null);
        if ($error !== null) {
            Session::flash('error', $error);
            Session::flash('child_form_old', $input);
            Response::redirect($this->familyShowUrl($familyId, 'composition', 'child'));
        }

        try {
            if ($this->familyModel()->findById($familyId) === null) {
                Session::flash('error', 'Familia nao encontrada.');
                Response::redirect('/families');
            }

            $this->compositionService()->createChild($input);
        } catch (Throwable) {
            Session::flash('error', 'Falha ao adicionar crianca.');
            Session::flash('child_form_old', $input);
            Response::redirect($this->familyShowUrl($familyId, 'composition', 'child'));
        }

        Session::flash('success', 'Crianca adicionada com sucesso.');
        Response::redirect($this->familyShowUrl($familyId, 'composition', 'child'));
    }

    public function updateChild(): void
    {
        $familyId = (int) ($_GET['family_id'] ?? 0);
        $childId = (int) ($_GET['id'] ?? 0);
        if ($familyId <= 0 || $childId <= 0) {
            Session::flash('error', 'Crianca invalida.');
            Response::redirect('/families');
        }

        $input = $this->compositionService()->sanitizeChildInput($_POST, $familyId);
        $error = $this->compositionService()->validateChildInput($input, $childId);
        if ($error !== null) {
            Session::flash('error', $error);
            $input['id'] = $childId;
            Session::flash('child_form_old', $input);
            Response::redirect($this->familyShowUrl($familyId, 'composition', 'child', ['child_edit' => $childId]));
        }

        try {
            $child = $this->childModel()->findById($childId);
            if ($child === null || (int) ($child['family_id'] ?? 0) !== $familyId) {
                Session::flash('error', 'Crianca nao encontrada.');
                Response::redirect($this->familyShowUrl($familyId, 'composition', 'child'));
            }

            $this->compositionService()->updateChild($childId, $input);
        } catch (Throwable) {
            Session::flash('error', 'Falha ao atualizar crianca.');
            $input['id'] = $childId;
            Session::flash('child_form_old', $input);
            Response::redirect($this->familyShowUrl($familyId, 'composition', 'child', ['child_edit' => $childId]));
        }

        Session::flash('success', 'Crianca atualizada com sucesso.');
        Response::redirect($this->familyShowUrl($familyId, 'composition', 'child'));
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
                Response::redirect($this->familyShowUrl($familyId, 'composition', 'child'));
            }

            $this->compositionService()->deleteChild($childId, $familyId);
        } catch (Throwable) {
            Session::flash('error', 'Falha ao remover crianca.');
            Response::redirect($this->familyShowUrl($familyId, 'composition', 'child'));
        }

        Session::flash('success', 'Crianca removida com sucesso.');
        Response::redirect($this->familyShowUrl($familyId, 'composition', 'child'));
    }

    private function renderForm(string $mode, array $family): void
    {
        $registration = $this->registrationService();

        View::render('families.form', [
            '_layout' => 'layouts.app',
            'appName' => (string) ($this->container->get('config')['app']['name'] ?? 'Dashboard PHP PBT'),
            'pageTitle' => $mode === 'edit' ? 'Editar familia' : 'Nova familia',
            'activeMenu' => 'familias',
            'authUser' => Session::get('auth_user', []),
            'mode' => $mode,
            'family' => $family,
            'docStatuses' => FamilyRegistrationService::DOC_STATUSES,
            'housingTypes' => $registration->withLegacyOption(FamilyRegistrationService::HOUSING_TYPES, (string) ($family['housing_type'] ?? '')),
            'maritalStatuses' => $registration->withLegacyOption(FamilyRegistrationService::MARITAL_STATUSES, (string) ($family['marital_status'] ?? '')),
            'educationLevels' => $registration->withLegacyOption(FamilyRegistrationService::EDUCATION_LEVELS, (string) ($family['education_level'] ?? '')),
            'professionalStatuses' => $registration->withLegacyOption(FamilyRegistrationService::PROFESSIONAL_STATUSES, (string) ($family['professional_status'] ?? '')),
            'error' => Session::consumeFlash('error'),
        ]);
    }

    private function familyShowUrl(int $familyId, string $tab = 'composition', string $personType = '', array $extra = []): string
    {
        $query = [
            'id' => $familyId,
            'tab' => $tab,
        ];

        $sanitizedPersonType = $this->compositionService()->sanitizePersonType($personType);
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

    private function registrationService(): FamilyRegistrationService
    {
        return new FamilyRegistrationService($this->familyModel(), $this->indicatorsService());
    }

    private function compositionService(): FamilyCompositionService
    {
        return new FamilyCompositionService($this->familyModel(), $this->childModel(), $this->indicatorsService());
    }

    private function detailService(): FamilyDetailService
    {
        return new FamilyDetailService(
            $this->familyModel(),
            $this->childModel(),
            $this->deliveryModel(),
            $this->equipmentLoanModel(),
            $this->visitModel(),
            $this->compositionService(),
        );
    }

    private function indicatorsService(): FamilyIndicatorsService
    {
        return new FamilyIndicatorsService($this->familyModel());
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

    private function deliveryModel(): DeliveryModel
    {
        /** @var PDO $pdo */
        $pdo = $this->container->get('db');
        return new DeliveryModel($pdo);
    }

    private function equipmentLoanModel(): EquipmentLoanModel
    {
        /** @var PDO $pdo */
        $pdo = $this->container->get('db');
        return new EquipmentLoanModel($pdo);
    }

    private function visitModel(): VisitModel
    {
        /** @var PDO $pdo */
        $pdo = $this->container->get('db');
        return new VisitModel($pdo);
    }
}
