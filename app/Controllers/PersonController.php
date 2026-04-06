<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Container;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\PersonModel;
use App\Models\FamilyModel;
use App\Models\ReferralModel;
use App\Models\SocialRecordModel;
use App\Models\SpiritualFollowupModel;
use App\Services\CpfService;
use App\Services\FamilyDataSupport;
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
            'pageTitle' => 'PROJETO AMOR',
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
            $personId = $this->personModel()->create($this->toPersistenceData($input));
            $this->personModel()->replacePhones($personId, $input['phones'] ?? []);
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
            if (is_array($person)) {
                $person['phones'] = FamilyDataSupport::fallbackPhoneEntries(
                    $this->personModel()->getPhones($id),
                    (string) ($person['phone'] ?? '')
                );
            }
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
            $this->personModel()->replacePhones($id, $input['phones'] ?? []);
        } catch (Throwable $exception) {
            Session::flash('error', 'Falha ao atualizar pessoa acompanhada.');
            Session::flash('form_old', $input);
            Response::redirect('/people/edit?id=' . $id);
        }

        Session::flash('success', 'Pessoa acompanhada atualizada com sucesso.');
        Response::redirect('/people');
    }

    public function delete(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            Session::flash('error', 'Pessoa invalida.');
            Response::redirect('/people');
        }

        try {
            if ($this->personModel()->findById($id) === null) {
                Session::flash('error', 'Pessoa nao encontrada.');
                Response::redirect('/people');
            }

            $records = $this->socialRecordModel()->findByPersonId($id);
            foreach ($records as $record) {
                $recordId = (int) ($record['id'] ?? 0);
                if ($recordId > 0) {
                    $this->socialRecordModel()->delete($recordId, $id);
                }
            }

            $this->personModel()->delete($id);
        } catch (Throwable $exception) {
            Session::flash('error', 'Falha ao remover pessoa acompanhada.');
            Response::redirect('/people');
        }

        Session::flash('success', 'Pessoa acompanhada removida com sucesso.');
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
            $person['phones'] = FamilyDataSupport::fallbackPhoneEntries(
                $this->personModel()->getPhones($id),
                (string) ($person['phone'] ?? '')
            );
            $person['chronic_disease_list'] = FamilyDataSupport::parseChronicDiseases($person['chronic_disease'] ?? []);
            $person['chronic_disease_labels'] = FamilyDataSupport::chronicDiseaseLabels($person['chronic_disease_list']);

            $timeline = $this->socialRecordModel()->findByPersonId($id);
            $families = $this->familyModel()->search([]);
            $referralFilters = [
                'referral_type' => trim((string) ($_GET['referral_type'] ?? '')),
                'status' => trim((string) ($_GET['referral_status'] ?? '')),
            ];
            $spiritualFilters = [
                'action' => trim((string) ($_GET['spiritual_action'] ?? '')),
            ];
            $referrals = $this->referralModel()->findByPersonId($id, $referralFilters);
            $spiritualFollowups = $this->spiritualFollowupModel()->findByPersonId($id, $spiritualFilters);
        } catch (Throwable $exception) {
            Session::flash('error', 'Falha ao carregar detalhe da pessoa.');
            Response::redirect('/people');
        }

        $recordOld = Session::consumeFlash('record_form_old');
        $recordForm = $this->defaultSocialRecordFormData();
        if (is_array($recordOld)) {
            $recordForm = array_merge($recordForm, $recordOld);
        }

        $recordEditId = (int) ($_GET['record_edit'] ?? 0);
        $recordEdit = null;
        if ($recordEditId > 0) {
            foreach ($timeline as $record) {
                if ((int) ($record['id'] ?? 0) === $recordEditId) {
                    $recordEdit = $record;
                    break;
                }
            }
        }
        if ($recordEdit !== null && !is_array($recordOld)) {
            $recordForm = array_merge($recordForm, $recordEdit);
        }

        $referralOld = Session::consumeFlash('referral_form_old');
        $referralForm = $this->defaultReferralFormData();
        if (is_array($referralOld)) {
            $referralForm = array_merge($referralForm, $referralOld);
        }

        $spiritualOld = Session::consumeFlash('spiritual_form_old');
        $spiritualForm = $this->defaultSpiritualFormData();
        if (is_array($spiritualOld)) {
            $spiritualForm = array_merge($spiritualForm, $spiritualOld);
        }

        $referralEditId = (int) ($_GET['referral_edit'] ?? 0);
        $referralEdit = null;
        if ($referralEditId > 0) {
            foreach ($referrals as $r) {
                if ((int) ($r['id'] ?? 0) === $referralEditId) {
                    $referralEdit = $r;
                    break;
                }
            }
        }
        if ($referralEdit !== null && !is_array($referralOld)) {
            $referralForm = array_merge($referralForm, $referralEdit);
        }

        $spiritualEditId = (int) ($_GET['spiritual_edit'] ?? 0);
        $spiritualEdit = null;
        if ($spiritualEditId > 0) {
            foreach ($spiritualFollowups as $s) {
                if ((int) ($s['id'] ?? 0) === $spiritualEditId) {
                    $spiritualEdit = $s;
                    break;
                }
            }
        }
        if ($spiritualEdit !== null && !is_array($spiritualOld)) {
            $spiritualForm = array_merge($spiritualForm, $spiritualEdit);
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
            'referrals' => $referrals,
            'spiritualFollowups' => $spiritualFollowups,
            'referralFilters' => $referralFilters,
            'spiritualFilters' => $spiritualFilters,
            'recordForm' => $recordForm,
            'recordEditMode' => $recordEdit !== null,
            'referralForm' => $referralForm,
            'spiritualForm' => $spiritualForm,
            'referralEditMode' => $referralEdit !== null,
            'spiritualEditMode' => $spiritualEdit !== null,
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

    public function updateSocialRecord(): void
    {
        $personId = (int) ($_GET['person_id'] ?? 0);
        $recordId = (int) ($_GET['id'] ?? 0);
        if ($personId <= 0 || $recordId <= 0) {
            Session::flash('error', 'Atendimento invalido.');
            Response::redirect('/people');
        }

        $input = $this->sanitizeSocialRecordInput($_POST, $personId);
        $error = $this->validateSocialRecordInput($input);
        if ($error !== null) {
            Session::flash('error', $error);
            $input['id'] = $recordId;
            Session::flash('record_form_old', $input);
            Response::redirect('/people/show?id=' . $personId . '&record_edit=' . $recordId);
        }

        try {
            $existing = $this->socialRecordModel()->findById($recordId);
            if ($existing === null || (int) ($existing['person_id'] ?? 0) !== $personId) {
                Session::flash('error', 'Atendimento nao encontrado.');
                Response::redirect('/people/show?id=' . $personId);
            }

            if (!empty($input['family_id']) && $this->familyModel()->findById((int) $input['family_id']) === null) {
                Session::flash('error', 'Familia vinculada nao encontrada.');
                $input['id'] = $recordId;
                Session::flash('record_form_old', $input);
                Response::redirect('/people/show?id=' . $personId . '&record_edit=' . $recordId);
            }

            $this->socialRecordModel()->update(
                $recordId,
                $personId,
                $this->toSocialRecordUpdatePersistenceData($input)
            );
        } catch (Throwable $exception) {
            Session::flash('error', 'Falha ao atualizar atendimento social.');
            Response::redirect('/people/show?id=' . $personId . '&record_edit=' . $recordId);
        }

        Session::flash('success', 'Atendimento social atualizado com sucesso.');
        Response::redirect('/people/show?id=' . $personId);
    }

    public function deleteSocialRecord(): void
    {
        $personId = (int) ($_GET['person_id'] ?? 0);
        $recordId = (int) ($_GET['id'] ?? 0);
        if ($personId <= 0 || $recordId <= 0) {
            Session::flash('error', 'Atendimento invalido.');
            Response::redirect('/people');
        }

        try {
            $existing = $this->socialRecordModel()->findById($recordId);
            if ($existing === null || (int) ($existing['person_id'] ?? 0) !== $personId) {
                Session::flash('error', 'Atendimento nao encontrado.');
                Response::redirect('/people/show?id=' . $personId);
            }
            $this->socialRecordModel()->delete($recordId, $personId);
        } catch (Throwable $exception) {
            Session::flash('error', 'Falha ao remover atendimento social.');
            Response::redirect('/people/show?id=' . $personId);
        }

        Session::flash('success', 'Atendimento social removido com sucesso.');
        Response::redirect('/people/show?id=' . $personId);
    }

    public function storeReferral(): void
    {
        $personId = (int) ($_GET['person_id'] ?? 0);
        $input = $this->sanitizeReferralInput($_POST);
        $error = $this->validateReferralInput($personId, $input);
        if ($error !== null) {
            Session::flash('error', $error);
            Session::flash('referral_form_old', $input);
            Response::redirect('/people/show?id=' . $personId);
        }

        try {
            $this->referralModel()->create($this->toReferralPersistenceData($input));
        } catch (Throwable $exception) {
            Session::flash('error', 'Falha ao registrar encaminhamento.');
            Session::flash('referral_form_old', $input);
            Response::redirect('/people/show?id=' . $personId);
        }

        Session::flash('success', 'Encaminhamento registrado com sucesso.');
        Response::redirect('/people/show?id=' . $personId);
    }

    public function updateReferral(): void
    {
        $personId = (int) ($_GET['person_id'] ?? 0);
        $referralId = (int) ($_GET['id'] ?? 0);
        $input = $this->sanitizeReferralInput($_POST);
        $error = $this->validateReferralInput($personId, $input);
        if ($error !== null) {
            Session::flash('error', $error);
            $input['id'] = $referralId;
            Session::flash('referral_form_old', $input);
            Response::redirect('/people/show?id=' . $personId . '&referral_edit=' . $referralId);
        }

        try {
            $existing = $this->referralModel()->findById($referralId);
            if ($existing === null || (int) ($existing['person_id'] ?? 0) !== $personId) {
                Session::flash('error', 'Encaminhamento nao encontrado.');
                Response::redirect('/people/show?id=' . $personId);
            }
            $this->referralModel()->update($referralId, $this->toReferralPersistenceData($input));
        } catch (Throwable $exception) {
            Session::flash('error', 'Falha ao atualizar encaminhamento.');
            Response::redirect('/people/show?id=' . $personId . '&referral_edit=' . $referralId);
        }

        Session::flash('success', 'Encaminhamento atualizado com sucesso.');
        Response::redirect('/people/show?id=' . $personId);
    }

    public function deleteReferral(): void
    {
        $personId = (int) ($_GET['person_id'] ?? 0);
        $referralId = (int) ($_GET['id'] ?? 0);

        try {
            $existing = $this->referralModel()->findById($referralId);
            if ($existing !== null && (int) ($existing['person_id'] ?? 0) === $personId) {
                $this->referralModel()->delete($referralId);
            }
        } catch (Throwable $exception) {
            Session::flash('error', 'Falha ao remover encaminhamento.');
            Response::redirect('/people/show?id=' . $personId);
        }

        Session::flash('success', 'Encaminhamento removido com sucesso.');
        Response::redirect('/people/show?id=' . $personId);
    }

    public function storeSpiritualFollowup(): void
    {
        $personId = (int) ($_GET['person_id'] ?? 0);
        $input = $this->sanitizeSpiritualInput($_POST, $personId);
        $error = $this->validateSpiritualInput($input);
        if ($error !== null) {
            Session::flash('error', $error);
            Session::flash('spiritual_form_old', $input);
            Response::redirect('/people/show?id=' . $personId);
        }

        try {
            $this->spiritualFollowupModel()->create($this->toSpiritualPersistenceData($input));
        } catch (Throwable $exception) {
            Session::flash('error', 'Falha ao registrar acompanhamento espiritual.');
            Session::flash('spiritual_form_old', $input);
            Response::redirect('/people/show?id=' . $personId);
        }

        Session::flash('success', 'Acompanhamento espiritual registrado com sucesso.');
        Response::redirect('/people/show?id=' . $personId);
    }

    public function updateSpiritualFollowup(): void
    {
        $personId = (int) ($_GET['person_id'] ?? 0);
        $id = (int) ($_GET['id'] ?? 0);
        $input = $this->sanitizeSpiritualInput($_POST, $personId);
        $error = $this->validateSpiritualInput($input);
        if ($error !== null) {
            Session::flash('error', $error);
            $input['id'] = $id;
            Session::flash('spiritual_form_old', $input);
            Response::redirect('/people/show?id=' . $personId . '&spiritual_edit=' . $id);
        }

        try {
            $existing = $this->spiritualFollowupModel()->findById($id);
            if ($existing === null || (int) ($existing['person_id'] ?? 0) !== $personId) {
                Session::flash('error', 'Acompanhamento espiritual nao encontrado.');
                Response::redirect('/people/show?id=' . $personId);
            }
            $this->spiritualFollowupModel()->update($id, $this->toSpiritualPersistenceData($input));
        } catch (Throwable $exception) {
            Session::flash('error', 'Falha ao atualizar acompanhamento espiritual.');
            Response::redirect('/people/show?id=' . $personId . '&spiritual_edit=' . $id);
        }

        Session::flash('success', 'Acompanhamento espiritual atualizado com sucesso.');
        Response::redirect('/people/show?id=' . $personId);
    }

    public function deleteSpiritualFollowup(): void
    {
        $personId = (int) ($_GET['person_id'] ?? 0);
        $id = (int) ($_GET['id'] ?? 0);

        try {
            $this->spiritualFollowupModel()->delete($id, $personId);
        } catch (Throwable $exception) {
            Session::flash('error', 'Falha ao remover acompanhamento espiritual.');
            Response::redirect('/people/show?id=' . $personId);
        }

        Session::flash('success', 'Acompanhamento espiritual removido com sucesso.');
        Response::redirect('/people/show?id=' . $personId);
    }

    private function renderForm(string $mode, array $person): void
    {
        $person['phones'] = FamilyDataSupport::fallbackPhoneEntries(
            is_array($person['phones'] ?? null) ? $person['phones'] : [],
            (string) ($person['phone'] ?? '')
        );

        View::render('people.form', [
            '_layout' => 'layouts.app',
            'appName' => (string) ($this->container->get('config')['app']['name'] ?? 'Dashboard PHP PBT'),
            'pageTitle' => $mode === 'edit' ? 'Editar pessoa acompanhada' : 'Nova pessoa acompanhada',
            'activeMenu' => 'pessoas',
            'authUser' => Session::get('auth_user', []),
            'mode' => $mode,
            'person' => $person,
            'chronicDiseaseOptions' => FamilyDataSupport::withLegacyOptions(
                FamilyDataSupport::CHRONIC_DISEASE_OPTIONS,
                FamilyDataSupport::parseChronicDiseases($person['chronic_disease'] ?? [])
            ),
            'socialBenefitOptions' => $this->withLegacyOption(FamilyDataSupport::SOCIAL_BENEFIT_OPTIONS, (string) ($person['social_benefit'] ?? '')),
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
            'phone' => '',
            'phones' => FamilyDataSupport::fallbackPhoneEntries([], null),
            'previous_address' => '',
            'has_family_in_region' => 0,
            'family_contact' => '',
            'education_level' => '',
            'profession_skills' => '',
            'formal_work_history' => 0,
            'work_interest' => 0,
            'work_interest_detail' => '',
            'chronic_disease' => [],
            'chronic_disease_other_details' => '',
            'has_physical_disability' => 0,
            'physical_disability_details' => '',
            'uses_continuous_medication' => 0,
            'continuous_medication_details' => '',
            'has_addiction' => 0,
            'addiction_details' => '',
            'social_benefit' => '',
        ];
    }

    private function sanitizeInput(array $post): array
    {
        $approxAge = trim((string) ($post['approx_age'] ?? ''));
        $birthDate = trim((string) ($post['birth_date'] ?? ''));
        $calculatedAge = FamilyDataSupport::calculateAgeFromBirthDate($birthDate);
        return [
            'full_name' => trim((string) ($post['full_name'] ?? '')),
            'social_name' => trim((string) ($post['social_name'] ?? '')),
            'cpf' => trim((string) ($post['cpf'] ?? '')),
            'rg' => trim((string) ($post['rg'] ?? '')),
            'birth_date' => $birthDate,
            'approx_age' => $calculatedAge ?? ($approxAge === '' ? null : max(0, (int) $approxAge)),
            'gender' => trim((string) ($post['gender'] ?? '')),
            'is_homeless' => isset($post['is_homeless']) ? 1 : 0,
            'homeless_time' => trim((string) ($post['homeless_time'] ?? '')),
            'stay_location' => trim((string) ($post['stay_location'] ?? '')),
            'phones' => FamilyDataSupport::sanitizePhoneEntries($post['phones'] ?? []),
            'previous_address' => trim((string) ($post['previous_address'] ?? '')),
            'has_family_in_region' => isset($post['has_family_in_region']) ? 1 : 0,
            'family_contact' => trim((string) ($post['family_contact'] ?? '')),
            'education_level' => trim((string) ($post['education_level'] ?? '')),
            'profession_skills' => trim((string) ($post['profession_skills'] ?? '')),
            'formal_work_history' => isset($post['formal_work_history']) ? 1 : 0,
            'work_interest' => isset($post['work_interest']) ? 1 : 0,
            'work_interest_detail' => trim((string) ($post['work_interest_detail'] ?? '')),
            'chronic_disease' => FamilyDataSupport::sanitizeChronicDiseases($post['chronic_disease'] ?? []),
            'chronic_disease_other_details' => trim((string) ($post['chronic_disease_other_details'] ?? '')),
            'has_physical_disability' => FamilyDataSupport::sanitizeBooleanFlag($post['has_physical_disability'] ?? 0),
            'physical_disability_details' => trim((string) ($post['physical_disability_details'] ?? '')),
            'uses_continuous_medication' => FamilyDataSupport::sanitizeBooleanFlag($post['uses_continuous_medication'] ?? 0),
            'continuous_medication_details' => trim((string) ($post['continuous_medication_details'] ?? '')),
            'has_addiction' => FamilyDataSupport::sanitizeBooleanFlag($post['has_addiction'] ?? 0),
            'addiction_details' => trim((string) ($post['addiction_details'] ?? '')),
            'social_benefit' => trim((string) ($post['social_benefit'] ?? '')),
            'phone' => '',
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
                $conflict = $this->familyModel()->findCpfConflict((string) $input['cpf'], [
                    'person_id' => $excludeId ?? 0,
                ]);
            } catch (Throwable $exception) {
                return 'Falha ao validar duplicidade de CPF.';
            }

            if ($conflict !== null) {
                return $this->buildCpfConflictMessage($conflict);
            }
        }

        $existingPerson = null;
        if ($excludeId !== null && $excludeId > 0) {
            try {
                $existingPerson = $this->personModel()->findById($excludeId);
            } catch (Throwable $exception) {
                return 'Falha ao validar cadastro existente.';
            }
        }

        $fieldConfig = [
            'social_benefit' => ['options' => array_keys(FamilyDataSupport::SOCIAL_BENEFIT_OPTIONS), 'label' => 'Beneficio social'],
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

            $legacyValue = trim((string) ($existingPerson[$field] ?? ''));
            if ($legacyValue !== '' && $legacyValue === $value) {
                continue;
            }

            return (string) ($config['label'] ?? 'Campo') . ' invalido.';
        }

        foreach (($input['chronic_disease'] ?? []) as $disease) {
            if (isset(FamilyDataSupport::CHRONIC_DISEASE_OPTIONS[$disease])) {
                continue;
            }

            $legacyValues = FamilyDataSupport::parseChronicDiseases($existingPerson['chronic_disease'] ?? []);
            if (in_array($disease, $legacyValues, true)) {
                continue;
            }

            return 'Doenca cronica invalida.';
        }

        if (FamilyDataSupport::chronicDiseaseOtherDetailsRequired($input['chronic_disease'] ?? [])
            && trim((string) ($input['chronic_disease_other_details'] ?? '')) === '') {
            return 'Informe qual doenca cronica em "Outra".';
        }

        if ((int) ($input['has_addiction'] ?? 0) === 1 && trim((string) ($input['addiction_details'] ?? '')) === '') {
            return 'Informe qual vicio quando selecionar "Tem algum vicio?".';
        }

        return null;
    }

    private function buildCpfConflictMessage(array $conflict): string
    {
        $source = (string) ($conflict['source_table'] ?? '');
        $name = trim((string) ($conflict['source_name'] ?? ''));

        $target = match ($source) {
            'families' => 'familia',
            'family_members' => 'membro da familia',
            'children' => 'crianca',
            'people' => 'pessoa',
            default => 'cadastro existente',
        };

        if ($name !== '') {
            return 'CPF ja cadastrado em ' . $target . ': ' . $name . '.';
        }

        return 'CPF ja cadastrado no sistema.';
    }

    private function toPersistenceData(array $input): array
    {
        $input['phones'] = FamilyDataSupport::sanitizePhoneEntries($input['phones'] ?? []);
        $input['phone'] = FamilyDataSupport::primaryPhoneFromEntries($input['phones']);

        if ((int) ($input['has_physical_disability'] ?? 0) !== 1) {
            $input['physical_disability_details'] = '';
        }
        if ((int) ($input['uses_continuous_medication'] ?? 0) !== 1) {
            $input['continuous_medication_details'] = '';
        }
        if ((int) ($input['has_addiction'] ?? 0) !== 1) {
            $input['addiction_details'] = '';
        }
        if (!FamilyDataSupport::chronicDiseaseOtherDetailsRequired($input['chronic_disease'] ?? [])) {
            $input['chronic_disease_other_details'] = '';
        }

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
            'phone' => $input['phone'] !== '' ? $input['phone'] : null,
            'previous_address' => $input['previous_address'] !== '' ? $input['previous_address'] : null,
            'has_family_in_region' => (int) $input['has_family_in_region'],
            'family_contact' => $input['family_contact'] !== '' ? $input['family_contact'] : null,
            'education_level' => $input['education_level'] !== '' ? $input['education_level'] : null,
            'profession_skills' => $input['profession_skills'] !== '' ? $input['profession_skills'] : null,
            'formal_work_history' => (int) $input['formal_work_history'],
            'work_interest' => (int) $input['work_interest'],
            'work_interest_detail' => $input['work_interest_detail'] !== '' ? $input['work_interest_detail'] : null,
            'chronic_disease' => FamilyDataSupport::encodeChronicDiseases($input['chronic_disease'] ?? []),
            'chronic_disease_other_details' => $input['chronic_disease_other_details'] !== '' ? $input['chronic_disease_other_details'] : null,
            'has_physical_disability' => (int) ($input['has_physical_disability'] ?? 0),
            'physical_disability_details' => $input['physical_disability_details'] !== '' ? $input['physical_disability_details'] : null,
            'uses_continuous_medication' => (int) ($input['uses_continuous_medication'] ?? 0),
            'continuous_medication_details' => $input['continuous_medication_details'] !== '' ? $input['continuous_medication_details'] : null,
            'has_addiction' => (int) ($input['has_addiction'] ?? 0),
            'addiction_details' => $input['addiction_details'] !== '' ? $input['addiction_details'] : null,
            'social_benefit' => $input['social_benefit'] !== '' ? $input['social_benefit'] : null,
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

    private function referralModel(): ReferralModel
    {
        /** @var PDO $pdo */
        $pdo = $this->container->get('db');
        return new ReferralModel($pdo);
    }

    private function spiritualFollowupModel(): SpiritualFollowupModel
    {
        /** @var PDO $pdo */
        $pdo = $this->container->get('db');
        return new SpiritualFollowupModel($pdo);
    }

    private function defaultSocialRecordFormData(): array
    {
        return [
            'id' => 0,
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

    private function defaultReferralFormData(): array
    {
        return [
            'social_record_id' => 0,
            'referral_type' => '',
            'referral_date' => date('Y-m-d'),
            'status' => 'encaminhado',
            'notes' => '',
        ];
    }

    private function defaultSpiritualFormData(): array
    {
        return [
            'followup_date' => date('Y-m-d'),
            'action' => '',
            'notes' => '',
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

    private function toSocialRecordUpdatePersistenceData(array $input): array
    {
        return [
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
        ];
    }

    private function sanitizeReferralInput(array $post): array
    {
        return [
            'social_record_id' => (int) ($post['social_record_id'] ?? 0),
            'referral_type' => trim((string) ($post['referral_type'] ?? '')),
            'referral_date' => trim((string) ($post['referral_date'] ?? '')),
            'status' => trim((string) ($post['status'] ?? 'encaminhado')),
            'notes' => trim((string) ($post['notes'] ?? '')),
        ];
    }

    private function validateReferralInput(int $personId, array $input): ?string
    {
        if ($personId <= 0) {
            return 'Pessoa invalida.';
        }
        if ((int) ($input['social_record_id'] ?? 0) <= 0) {
            return 'Selecione um atendimento para vincular o encaminhamento.';
        }
        if (trim((string) ($input['referral_type'] ?? '')) === '') {
            return 'Tipo de encaminhamento obrigatorio.';
        }
        if (trim((string) ($input['referral_date'] ?? '')) === '') {
            return 'Data do encaminhamento obrigatoria.';
        }

        try {
            $record = $this->socialRecordModel()->findById((int) $input['social_record_id']);
        } catch (Throwable $exception) {
            return 'Falha ao validar atendimento vinculado.';
        }
        if ($record === null || (int) ($record['person_id'] ?? 0) !== $personId) {
            return 'O encaminhamento deve estar vinculado a um atendimento da propria pessoa.';
        }

        return null;
    }

    private function toReferralPersistenceData(array $input): array
    {
        $authUser = Session::get('auth_user', []);
        $userId = is_array($authUser) ? (int) ($authUser['id'] ?? 0) : 0;

        return [
            'social_record_id' => (int) $input['social_record_id'],
            'referral_type' => $input['referral_type'],
            'referral_date' => $input['referral_date'],
            'responsible_user_id' => $userId,
            'status' => $input['status'] !== '' ? $input['status'] : 'encaminhado',
            'notes' => $input['notes'] !== '' ? $input['notes'] : null,
        ];
    }

    private function sanitizeSpiritualInput(array $post, int $personId): array
    {
        return [
            'person_id' => $personId,
            'followup_date' => trim((string) ($post['followup_date'] ?? '')),
            'action' => trim((string) ($post['action'] ?? '')),
            'notes' => trim((string) ($post['notes'] ?? '')),
        ];
    }

    private function validateSpiritualInput(array $input): ?string
    {
        if ((int) ($input['person_id'] ?? 0) <= 0) {
            return 'Pessoa invalida.';
        }
        if (trim((string) ($input['followup_date'] ?? '')) === '') {
            return 'Data do acompanhamento espiritual obrigatoria.';
        }
        if (trim((string) ($input['action'] ?? '')) === '') {
            return 'Acao do acompanhamento espiritual obrigatoria.';
        }
        return null;
    }

    private function toSpiritualPersistenceData(array $input): array
    {
        $authUser = Session::get('auth_user', []);
        $userId = is_array($authUser) ? (int) ($authUser['id'] ?? 0) : 0;

        return [
            'person_id' => (int) $input['person_id'],
            'followup_date' => $input['followup_date'],
            'action' => $input['action'],
            'notes' => $input['notes'] !== '' ? $input['notes'] : null,
            'created_by' => $userId,
        ];
    }
}
