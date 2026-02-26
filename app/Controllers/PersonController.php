<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Container;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\PersonModel;
use App\Models\FamilyModel;
use App\Models\SocialRecordModel;
use App\Services\CpfService;
use PDO;
use Throwable;

final class PersonController
{
    public function __construct(private readonly Container $container)
    {
    }

    public function index(): void
    {
        $filters = [
            'q' => trim((string) ($_GET['q'] ?? '')),
            'is_homeless' => trim((string) ($_GET['is_homeless'] ?? '')),
            'work_interest' => trim((string) ($_GET['work_interest'] ?? '')),
        ];

        try {
            $people = $this->personModel()->search($filters);
        } catch (Throwable $exception) {
            $people = [];
        }

        View::render('people.index', [
            '_layout' => 'layouts.app',
            'appName' => (string) ($this->container->get('config')['app']['name'] ?? 'Dashboard PHP PBT'),
            'pageTitle' => 'Pessoas acompanhadas',
            'activeMenu' => 'pessoas',
            'authUser' => Session::get('auth_user', []),
            'people' => $people,
            'filters' => $filters,
            'success' => Session::consumeFlash('success'),
            'error' => Session::consumeFlash('error'),
        ]);
    }

    public function create(): void
    {
        $old = Session::consumeFlash('form_old');
        $person = $this->defaultFormData();
        if (is_array($old)) {
            $person = array_merge($person, $old);
        }

        $this->renderForm('create', $person);
    }

    public function store(): void
    {
        $input = $this->sanitizeInput($_POST);
        $error = $this->validateInput($input, null);
        if ($error !== null) {
            Session::flash('error', $error);
            Session::flash('form_old', $input);
            Response::redirect('/people/create');
        }

        try {
            $this->personModel()->create($this->toPersistenceData($input));
        } catch (Throwable $exception) {
            Session::flash('error', 'Falha ao cadastrar pessoa acompanhada.');
            Session::flash('form_old', $input);
            Response::redirect('/people/create');
        }

        Session::flash('success', 'Pessoa acompanhada cadastrada com sucesso.');
        Response::redirect('/people');
    }

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            Session::flash('error', 'Pessoa invalida.');
            Response::redirect('/people');
        }

        try {
            $person = $this->personModel()->findById($id);
        } catch (Throwable $exception) {
            Session::flash('error', 'Falha ao carregar cadastro.');
            Response::redirect('/people');
        }

        if ($person === null) {
            Session::flash('error', 'Pessoa nao encontrada.');
            Response::redirect('/people');
        }

        $old = Session::consumeFlash('form_old');
        if (is_array($old)) {
            $person = array_merge($person, $old);
        }

        $this->renderForm('edit', $person);
    }

    public function update(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            Session::flash('error', 'Pessoa invalida.');
            Response::redirect('/people');
        }

        $input = $this->sanitizeInput($_POST);
        $error = $this->validateInput($input, $id);
        if ($error !== null) {
            Session::flash('error', $error);
            Session::flash('form_old', $input);
            Response::redirect('/people/edit?id=' . $id);
        }

        try {
            if ($this->personModel()->findById($id) === null) {
                Session::flash('error', 'Pessoa nao encontrada.');
                Response::redirect('/people');
            }

            $this->personModel()->update($id, $this->toPersistenceData($input));
        } catch (Throwable $exception) {
            Session::flash('error', 'Falha ao atualizar pessoa acompanhada.');
            Session::flash('form_old', $input);
            Response::redirect('/people/edit?id=' . $id);
        }

        Session::flash('success', 'Pessoa acompanhada atualizada com sucesso.');
        Response::redirect('/people');
    }

    public function show(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            Session::flash('error', 'Pessoa invalida.');
            Response::redirect('/people');
        }

        try {
            $person = $this->personModel()->findById($id);
            if ($person === null) {
                Session::flash('error', 'Pessoa nao encontrada.');
                Response::redirect('/people');
            }

            $timeline = $this->socialRecordModel()->findByPersonId($id);
            $families = $this->familyModel()->search([]);
        } catch (Throwable $exception) {
            Session::flash('error', 'Falha ao carregar detalhe da pessoa.');
            Response::redirect('/people');
        }

        $recordOld = Session::consumeFlash('record_form_old');
        $recordForm = $this->defaultSocialRecordFormData();
        if (is_array($recordOld)) {
            $recordForm = array_merge($recordForm, $recordOld);
        }

        View::render('people.show', [
            '_layout' => 'layouts.app',
            'appName' => (string) ($this->container->get('config')['app']['name'] ?? 'Dashboard PHP PBT'),
            'pageTitle' => 'Detalhe da pessoa',
            'activeMenu' => 'pessoas',
            'authUser' => Session::get('auth_user', []),
            'person' => $person,
            'timeline' => $timeline,
            'families' => $families,
            'recordForm' => $recordForm,
            'success' => Session::consumeFlash('success'),
            'error' => Session::consumeFlash('error'),
        ]);
    }

    public function storeSocialRecord(): void
    {
        $personId = (int) ($_GET['person_id'] ?? 0);
        if ($personId <= 0) {
            Session::flash('error', 'Pessoa invalida para atendimento.');
            Response::redirect('/people');
        }

        $input = $this->sanitizeSocialRecordInput($_POST, $personId);
        $error = $this->validateSocialRecordInput($input);
        if ($error !== null) {
            Session::flash('error', $error);
            Session::flash('record_form_old', $input);
            Response::redirect('/people/show?id=' . $personId);
        }

        try {
            if ($this->personModel()->findById($personId) === null) {
                Session::flash('error', 'Pessoa nao encontrada.');
                Response::redirect('/people');
            }

            if (!empty($input['family_id']) && $this->familyModel()->findById((int) $input['family_id']) === null) {
                Session::flash('error', 'Familia vinculada nao encontrada.');
                Session::flash('record_form_old', $input);
                Response::redirect('/people/show?id=' . $personId);
            }

            $this->socialRecordModel()->create($this->toSocialRecordPersistenceData($input));
        } catch (Throwable $exception) {
            Session::flash('error', 'Falha ao registrar atendimento social.');
            Session::flash('record_form_old', $input);
            Response::redirect('/people/show?id=' . $personId);
        }

        Session::flash('success', 'Atendimento social registrado com sucesso.');
        Response::redirect('/people/show?id=' . $personId);
    }

    private function renderForm(string $mode, array $person): void
    {
        View::render('people.form', [
            '_layout' => 'layouts.app',
            'appName' => (string) ($this->container->get('config')['app']['name'] ?? 'Dashboard PHP PBT'),
            'pageTitle' => $mode === 'edit' ? 'Editar pessoa acompanhada' : 'Nova pessoa acompanhada',
            'activeMenu' => 'pessoas',
            'authUser' => Session::get('auth_user', []),
            'mode' => $mode,
            'person' => $person,
            'error' => Session::consumeFlash('error'),
        ]);
    }

    private function defaultFormData(): array
    {
        return [
            'full_name' => '',
            'social_name' => '',
            'cpf' => '',
            'rg' => '',
            'birth_date' => '',
            'approx_age' => '',
            'gender' => '',
            'is_homeless' => 0,
            'homeless_time' => '',
            'stay_location' => '',
            'has_family_in_region' => 0,
            'family_contact' => '',
            'education_level' => '',
            'profession_skills' => '',
            'formal_work_history' => 0,
            'work_interest' => 0,
            'work_interest_detail' => '',
        ];
    }

    private function sanitizeInput(array $post): array
    {
        $approxAge = trim((string) ($post['approx_age'] ?? ''));
        return [
            'full_name' => trim((string) ($post['full_name'] ?? '')),
            'social_name' => trim((string) ($post['social_name'] ?? '')),
            'cpf' => trim((string) ($post['cpf'] ?? '')),
            'rg' => trim((string) ($post['rg'] ?? '')),
            'birth_date' => trim((string) ($post['birth_date'] ?? '')),
            'approx_age' => $approxAge === '' ? null : max(0, (int) $approxAge),
            'gender' => trim((string) ($post['gender'] ?? '')),
            'is_homeless' => isset($post['is_homeless']) ? 1 : 0,
            'homeless_time' => trim((string) ($post['homeless_time'] ?? '')),
            'stay_location' => trim((string) ($post['stay_location'] ?? '')),
            'has_family_in_region' => isset($post['has_family_in_region']) ? 1 : 0,
            'family_contact' => trim((string) ($post['family_contact'] ?? '')),
            'education_level' => trim((string) ($post['education_level'] ?? '')),
            'profession_skills' => trim((string) ($post['profession_skills'] ?? '')),
            'formal_work_history' => isset($post['formal_work_history']) ? 1 : 0,
            'work_interest' => isset($post['work_interest']) ? 1 : 0,
            'work_interest_detail' => trim((string) ($post['work_interest_detail'] ?? '')),
        ];
    }

    private function validateInput(array &$input, ?int $excludeId): ?string
    {
        // Permite dados incompletos, mas se CPF for informado deve ser valido e unico.
        if (($input['cpf'] ?? '') !== '') {
            if (!CpfService::isValid((string) $input['cpf'])) {
                return 'CPF invalido.';
            }

            $input['cpf'] = (string) CpfService::format((string) $input['cpf']);

            try {
                $duplicate = $this->personModel()->findByCpfExcludingId((string) $input['cpf'], $excludeId);
            } catch (Throwable $exception) {
                return 'Falha ao validar duplicidade de CPF.';
            }

            if ($duplicate !== null) {
                return 'Ja existe pessoa acompanhada com este CPF.';
            }
        }

        return null;
    }

    private function toPersistenceData(array $input): array
    {
        return [
            'full_name' => $input['full_name'] !== '' ? $input['full_name'] : null,
            'social_name' => $input['social_name'] !== '' ? $input['social_name'] : null,
            'cpf' => $input['cpf'] !== '' ? $input['cpf'] : null,
            'rg' => $input['rg'] !== '' ? $input['rg'] : null,
            'birth_date' => $input['birth_date'] !== '' ? $input['birth_date'] : null,
            'approx_age' => $input['approx_age'],
            'gender' => $input['gender'] !== '' ? $input['gender'] : null,
            'is_homeless' => (int) $input['is_homeless'],
            'homeless_time' => $input['homeless_time'] !== '' ? $input['homeless_time'] : null,
            'stay_location' => $input['stay_location'] !== '' ? $input['stay_location'] : null,
            'has_family_in_region' => (int) $input['has_family_in_region'],
            'family_contact' => $input['family_contact'] !== '' ? $input['family_contact'] : null,
            'education_level' => $input['education_level'] !== '' ? $input['education_level'] : null,
            'profession_skills' => $input['profession_skills'] !== '' ? $input['profession_skills'] : null,
            'formal_work_history' => (int) $input['formal_work_history'],
            'work_interest' => (int) $input['work_interest'],
            'work_interest_detail' => $input['work_interest_detail'] !== '' ? $input['work_interest_detail'] : null,
        ];
    }

    private function personModel(): PersonModel
    {
        /** @var PDO $pdo */
        $pdo = $this->container->get('db');
        return new PersonModel($pdo);
    }

    private function familyModel(): FamilyModel
    {
        /** @var PDO $pdo */
        $pdo = $this->container->get('db');
        return new FamilyModel($pdo);
    }

    private function socialRecordModel(): SocialRecordModel
    {
        /** @var PDO $pdo */
        $pdo = $this->container->get('db');
        return new SocialRecordModel($pdo);
    }

    private function defaultSocialRecordFormData(): array
    {
        return [
            'family_id' => 0,
            'chronic_diseases' => '',
            'continuous_medication' => '',
            'substance_use' => '',
            'disability' => '',
            'immediate_needs' => '',
            'spiritual_wants_prayer' => 0,
            'spiritual_accepts_visit' => 0,
            'church_name' => '',
            'spiritual_decision' => '',
            'notes' => '',
            'consent_text_version' => 'v1.0',
            'consent_name' => '',
        ];
    }

    private function sanitizeSocialRecordInput(array $post, int $personId): array
    {
        return [
            'person_id' => $personId,
            'family_id' => (int) ($post['family_id'] ?? 0),
            'chronic_diseases' => trim((string) ($post['chronic_diseases'] ?? '')),
            'continuous_medication' => trim((string) ($post['continuous_medication'] ?? '')),
            'substance_use' => trim((string) ($post['substance_use'] ?? '')),
            'disability' => trim((string) ($post['disability'] ?? '')),
            'immediate_needs' => trim((string) ($post['immediate_needs'] ?? '')),
            'spiritual_wants_prayer' => isset($post['spiritual_wants_prayer']) ? 1 : 0,
            'spiritual_accepts_visit' => isset($post['spiritual_accepts_visit']) ? 1 : 0,
            'church_name' => trim((string) ($post['church_name'] ?? '')),
            'spiritual_decision' => trim((string) ($post['spiritual_decision'] ?? '')),
            'notes' => trim((string) ($post['notes'] ?? '')),
            'consent_text_version' => trim((string) ($post['consent_text_version'] ?? 'v1.0')),
            'consent_name' => trim((string) ($post['consent_name'] ?? '')),
        ];
    }

    private function validateSocialRecordInput(array $input): ?string
    {
        if ((int) ($input['person_id'] ?? 0) <= 0) {
            return 'Pessoa invalida.';
        }

        if (trim((string) ($input['consent_name'] ?? '')) === '') {
            return 'Consentimento digital obrigatorio: informe o nome no termo.';
        }

        if (trim((string) ($input['consent_text_version'] ?? '')) === '') {
            return 'Versao do termo de consentimento obrigatoria.';
        }

        return null;
    }

    private function toSocialRecordPersistenceData(array $input): array
    {
        $authUser = Session::get('auth_user', []);
        $createdBy = is_array($authUser) ? (int) ($authUser['id'] ?? 0) : 0;

        return [
            'person_id' => (int) $input['person_id'],
            'family_id' => ((int) ($input['family_id'] ?? 0)) > 0 ? (int) $input['family_id'] : null,
            'chronic_diseases' => $input['chronic_diseases'] !== '' ? $input['chronic_diseases'] : null,
            'continuous_medication' => $input['continuous_medication'] !== '' ? $input['continuous_medication'] : null,
            'substance_use' => $input['substance_use'] !== '' ? $input['substance_use'] : null,
            'disability' => $input['disability'] !== '' ? $input['disability'] : null,
            'immediate_needs' => $input['immediate_needs'] !== '' ? $input['immediate_needs'] : null,
            'spiritual_wants_prayer' => (int) $input['spiritual_wants_prayer'],
            'spiritual_accepts_visit' => (int) $input['spiritual_accepts_visit'],
            'church_name' => $input['church_name'] !== '' ? $input['church_name'] : null,
            'spiritual_decision' => $input['spiritual_decision'] !== '' ? $input['spiritual_decision'] : null,
            'notes' => $input['notes'] !== '' ? $input['notes'] : null,
            'consent_text_version' => $input['consent_text_version'],
            'consent_name' => $input['consent_name'],
            'consent_at' => date('Y-m-d H:i:s'),
            'created_by' => $createdBy,
        ];
    }
}
