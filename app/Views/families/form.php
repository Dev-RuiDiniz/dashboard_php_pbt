<?php
declare(strict_types=1);

$isEdit = ($mode ?? 'create') === 'edit';
$familyData = is_array($family ?? null) ? $family : [];
$docStatuses = is_array($docStatuses ?? null) ? $docStatuses : ['ok', 'pendente', 'parcial'];
$housingTypes = is_array($housingTypes ?? null) ? $housingTypes : [];
$maritalStatuses = is_array($maritalStatuses ?? null) ? $maritalStatuses : [];
$educationLevels = is_array($educationLevels ?? null) ? $educationLevels : [];
$professionalStatuses = is_array($professionalStatuses ?? null) ? $professionalStatuses : [];
$chronicDiseaseOptions = is_array($chronicDiseaseOptions ?? null) ? $chronicDiseaseOptions : [];
$socialBenefitOptions = is_array($socialBenefitOptions ?? null) ? $socialBenefitOptions : [];
$phones = is_array($familyData['phones'] ?? null) ? $familyData['phones'] : [['number' => '', 'label' => '', 'is_primary' => 1]];
?>
<div class="row justify-content-center">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h2 class="h4 mb-1"><?= $isEdit ? 'Editar familia' : 'Nova familia' ?></h2>
                        <p class="text-secondary mb-0">Preencha rapido para concluir a entrevista.</p>
                    </div>
                    <a class="btn btn-outline-secondary" href="/families">Voltar</a>
                </div>

                <?php if (!empty($error)) : ?>
                    <div class="alert alert-danger border-0 shadow-sm"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
                <?php if (!$isEdit) : ?>
                    <div class="alert alert-light border-0 shadow-sm mb-3">
                        Depois de salvar, o sistema abre automaticamente o detalhe da familia na aba `Composicao Familiar` para cadastrar Principal, Membro e Crianca.
                    </div>
                <?php endif; ?>

                <form method="post" action="<?= $isEdit ? '/families/update?id=' . (int) ($familyData['id'] ?? 0) : '/families' ?>">
                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label">Numero da familia</label>
                            <input
                                type="text"
                                class="form-control"
                                value="<?= $isEdit ? ('#' . (int) ($familyData['id'] ?? 0)) : 'Sera gerado ao salvar' ?>"
                                readonly
                                tabindex="-1"
                            >
                        </div>
                        <div class="col-12 col-md-8 d-flex align-items-end">
                            <div class="text-secondary small">
                                Use o numero da familia como referencia rapida no atendimento e na conferencia.
                            </div>
                        </div>
                        <div class="col-12 col-lg-6">
                            <label class="form-label">Responsavel</label>
                            <input class="form-control" name="responsible_name" required autofocus placeholder="Nome completo da responsavel" value="<?= htmlspecialchars((string) ($familyData['responsible_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <label class="form-label">CPF</label>
                            <input class="form-control" name="cpf_responsible" required placeholder="000.000.000-00" value="<?= htmlspecialchars((string) ($familyData['cpf_responsible'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <label class="form-label">RG</label>
                            <input class="form-control" name="rg_responsible" placeholder="opcional" value="<?= htmlspecialchars((string) ($familyData['rg_responsible'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label d-flex justify-content-between align-items-center">
                                <span>Telefones</span>
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-phone-add data-phone-target="family-phones">Adicionar telefone</button>
                            </label>
                            <div class="vstack gap-2" id="family-phones">
                                <?php foreach ($phones as $index => $phoneEntry) : ?>
                                    <div class="border rounded p-2" data-phone-row>
                                        <div class="row g-2 align-items-end">
                                            <div class="col-12 col-md-5">
                                                <label class="form-label small">Numero</label>
                                                <input class="form-control" name="phones[<?= $index ?>][number]" placeholder="(00) 00000-0000" value="<?= htmlspecialchars((string) ($phoneEntry['number'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                            </div>
                                            <div class="col-12 col-md-5">
                                                <label class="form-label small">Identificacao / observacao</label>
                                                <input class="form-control" name="phones[<?= $index ?>][label]" placeholder="recado com filha, neta, vizinha..." value="<?= htmlspecialchars((string) ($phoneEntry['label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                            </div>
                                            <div class="col-6 col-md-1">
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="radio" name="phone_primary" value="<?= $index ?>" <?= ((int) ($phoneEntry['is_primary'] ?? 0) === 1) ? 'checked' : '' ?> data-phone-primary>
                                                    <label class="form-check-label small">Principal</label>
                                                </div>
                                                <input type="hidden" name="phones[<?= $index ?>][is_primary]" value="<?= ((int) ($phoneEntry['is_primary'] ?? 0) === 1) ? '1' : '0' ?>" data-phone-primary-hidden>
                                            </div>
                                            <div class="col-6 col-md-1 d-grid">
                                                <button type="button" class="btn btn-outline-danger btn-sm" data-phone-remove>Remover</button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <template id="family-phones-template">
                                <div class="border rounded p-2" data-phone-row>
                                    <div class="row g-2 align-items-end">
                                        <div class="col-12 col-md-5">
                                            <label class="form-label small">Numero</label>
                                            <input class="form-control" data-phone-number placeholder="(00) 00000-0000">
                                        </div>
                                        <div class="col-12 col-md-5">
                                            <label class="form-label small">Identificacao / observacao</label>
                                            <input class="form-control" data-phone-label placeholder="recado com filha, neta, vizinha...">
                                        </div>
                                        <div class="col-6 col-md-1">
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="radio" data-phone-primary>
                                                <label class="form-check-label small">Principal</label>
                                            </div>
                                            <input type="hidden" value="0" data-phone-primary-hidden>
                                        </div>
                                        <div class="col-6 col-md-1 d-grid">
                                            <button type="button" class="btn btn-outline-danger btn-sm" data-phone-remove>Remover</button>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label">Nascimento</label>
                            <input type="date" class="form-control" name="birth_date" value="<?= htmlspecialchars((string) ($familyData['birth_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="col-12 col-md-2">
                            <label class="form-label">Idade</label>
                            <input type="text" class="form-control" data-family-age-display readonly tabindex="-1" placeholder="Automatica">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Tipo de moradia</label>
                            <select class="form-select" name="housing_type" data-housing-type>
                                <option value="">Selecione</option>
                                <?php foreach ($housingTypes as $value => $label) : ?>
                                    <option value="<?= htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8') ?>" <?= ((string) ($familyData['housing_type'] ?? '') === (string) $value) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars((string) $label, ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-4 <?= ((string) ($familyData['housing_type'] ?? '') === 'alugada') ? '' : 'd-none' ?>" data-rent-amount-group>
                            <label class="form-label">Valor do aluguel</label>
                            <input class="form-control" name="rent_amount" placeholder="0,00" value="<?= htmlspecialchars((string) ($familyData['rent_amount'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>

                        <div class="col-12"><hr><h3 class="h6 text-uppercase text-secondary mb-0">Endereco</h3></div>

                        <div class="col-12 col-md-3">
                            <label class="form-label">CEP</label>
                            <input class="form-control" name="cep" placeholder="00000-000" value="<?= htmlspecialchars((string) ($familyData['cep'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                            <div class="form-text text-muted" data-cep-feedback></div>
                        </div>
                        <div class="col-12 col-md-7">
                            <label class="form-label">Logradouro</label>
                            <input class="form-control" name="address" value="<?= htmlspecialchars((string) ($familyData['address'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="col-12 col-md-2">
                            <label class="form-label">Numero</label>
                            <input class="form-control" name="address_number" value="<?= htmlspecialchars((string) ($familyData['address_number'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Complemento</label>
                            <input class="form-control" name="address_complement" value="<?= htmlspecialchars((string) ($familyData['address_complement'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Bairro</label>
                            <input class="form-control" name="neighborhood" value="<?= htmlspecialchars((string) ($familyData['neighborhood'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label">Cidade</label>
                            <input class="form-control" name="city" value="<?= htmlspecialchars((string) ($familyData['city'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="col-12 col-md-1">
                            <label class="form-label">UF</label>
                            <input class="form-control text-uppercase" maxlength="2" name="state" value="<?= htmlspecialchars((string) ($familyData['state'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Referencia</label>
                            <input class="form-control" name="location_reference" value="<?= htmlspecialchars((string) ($familyData['location_reference'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>

                        <div class="col-12"><hr><h3 class="h6 text-uppercase text-secondary mb-0">Socioeconomico</h3></div>

                        <div class="col-12 col-md-4">
                            <label class="form-label">Estado civil</label>
                            <select class="form-select" name="marital_status">
                                <option value="">Selecione</option>
                                <?php foreach ($maritalStatuses as $value => $label) : ?>
                                    <option value="<?= htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8') ?>" <?= ((string) ($familyData['marital_status'] ?? '') === (string) $value) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars((string) $label, ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Escolaridade</label>
                            <select class="form-select" name="education_level">
                                <option value="">Selecione</option>
                                <?php foreach ($educationLevels as $value => $label) : ?>
                                    <option value="<?= htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8') ?>" <?= ((string) ($familyData['education_level'] ?? '') === (string) $value) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars((string) $label, ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Situacao profissional</label>
                            <select class="form-select" name="professional_status">
                                <option value="">Selecione</option>
                                <?php foreach ($professionalStatuses as $value => $label) : ?>
                                    <option value="<?= htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8') ?>" <?= ((string) ($familyData['professional_status'] ?? '') === (string) $value) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars((string) $label, ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Profissao (detalhe opcional)</label>
                            <input class="form-control" name="profession_detail" value="<?= htmlspecialchars((string) ($familyData['profession_detail'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label">Adultos</label>
                            <input type="number" min="0" class="form-control" value="<?= htmlspecialchars((string) ($familyData['adults_count'] ?? 0), ENT_QUOTES, 'UTF-8') ?>" readonly tabindex="-1">
                            <div class="form-text">Calculado por membros.</div>
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label">Trabalhadores</label>
                            <input type="number" min="0" class="form-control" value="<?= htmlspecialchars((string) ($familyData['workers_count'] ?? 0), ENT_QUOTES, 'UTF-8') ?>" readonly tabindex="-1">
                            <div class="form-text">Calculado por membros.</div>
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label">Criancas</label>
                            <input type="number" class="form-control" value="<?= htmlspecialchars((string) ($familyData['children_count'] ?? 0), ENT_QUOTES, 'UTF-8') ?>" readonly tabindex="-1">
                            <div class="form-text">Automatico pela aba Familia.</div>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Renda familiar total</label>
                            <input class="form-control" value="<?= htmlspecialchars((string) ($familyData['family_income_total'] ?? '0.00'), ENT_QUOTES, 'UTF-8') ?>" readonly tabindex="-1">
                            <div class="form-text">Somatorio automatico da renda do principal e dos membros da familia.</div>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Media de renda per capita</label>
                            <input class="form-control" value="<?= htmlspecialchars((string) ($familyData['family_income_average'] ?? '0.00'), ENT_QUOTES, 'UTF-8') ?>" readonly tabindex="-1">
                            <div class="form-text">Calculada automaticamente pela composicao familiar.</div>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Documentacao</label>
                            <select class="form-select" name="documentation_status">
                                <?php foreach ($docStatuses as $docStatus) : ?>
                                    <option value="<?= htmlspecialchars((string) $docStatus, ENT_QUOTES, 'UTF-8') ?>" <?= ((string) ($familyData['documentation_status'] ?? 'ok') === (string) $docStatus) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars((string) $docStatus, ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-4 d-flex align-items-end">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="needs_visit" name="needs_visit" value="1" <?= ((int) ($familyData['needs_visit'] ?? 0) === 1) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="needs_visit">Necessita visita</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Pendencias de documentacao</label>
                            <textarea class="form-control" name="documentation_notes" rows="2"><?= htmlspecialchars((string) ($familyData['documentation_notes'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                        </div>
                        <div class="col-12">
                            <div class="alert alert-light border small mb-0">
                                Trabalho e renda do responsavel principal sao gerenciados na aba `Composicao Familiar`, junto com membros e criancas.
                            </div>
                        </div>
                        <div class="col-12"><hr><h3 class="h6 text-uppercase text-secondary mb-0">Saude e beneficios do responsavel</h3></div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Possui alguma Doenca Cronica?</label>
                            <select class="form-select" name="chronic_disease">
                                <option value="">Nao informado</option>
                                <?php foreach ($chronicDiseaseOptions as $value => $label) : ?>
                                    <option value="<?= htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8') ?>" <?= ((string) ($familyData['chronic_disease'] ?? '') === (string) $value) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars((string) $label, ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Possui alguma Deficiencia Fisica?</label>
                            <select class="form-select" name="has_physical_disability" data-conditional-toggle="family-disability-details">
                                <option value="0" <?= ((int) ($familyData['has_physical_disability'] ?? 0) !== 1) ? 'selected' : '' ?>>Nao</option>
                                <option value="1" <?= ((int) ($familyData['has_physical_disability'] ?? 0) === 1) ? 'selected' : '' ?>>Sim</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4 <?= ((int) ($familyData['has_physical_disability'] ?? 0) === 1) ? '' : 'd-none' ?>" data-conditional-group="family-disability-details">
                            <label class="form-label">Qual deficiencia?</label>
                            <input class="form-control" name="physical_disability_details" value="<?= htmlspecialchars((string) ($familyData['physical_disability_details'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Faz Uso de Medicacao Continua?</label>
                            <select class="form-select" name="uses_continuous_medication" data-conditional-toggle="family-medication-details">
                                <option value="0" <?= ((int) ($familyData['uses_continuous_medication'] ?? 0) !== 1) ? 'selected' : '' ?>>Nao</option>
                                <option value="1" <?= ((int) ($familyData['uses_continuous_medication'] ?? 0) === 1) ? 'selected' : '' ?>>Sim</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4 <?= ((int) ($familyData['uses_continuous_medication'] ?? 0) === 1) ? '' : 'd-none' ?>" data-conditional-group="family-medication-details">
                            <label class="form-label">Qual(is) medicacao(oes)?</label>
                            <input class="form-control" name="continuous_medication_details" value="<?= htmlspecialchars((string) ($familyData['continuous_medication_details'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Recebe Beneficio Social?</label>
                            <select class="form-select" name="social_benefit">
                                <option value="">Nao informado</option>
                                <?php foreach ($socialBenefitOptions as $value => $label) : ?>
                                    <option value="<?= htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8') ?>" <?= ((string) ($familyData['social_benefit'] ?? '') === (string) $value) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars((string) $label, ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Observacoes gerais</label>
                            <textarea class="form-control" name="general_notes" rows="3"><?= htmlspecialchars((string) ($familyData['general_notes'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" <?= ((int) ($familyData['is_active'] ?? 1) === 1) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="is_active">Cadastro ativo</label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2 mt-4">
                        <button type="submit" class="btn btn-teal text-white"><?= $isEdit ? 'Salvar alteracoes' : 'Salvar e continuar' ?></button>
                        <a class="btn btn-outline-secondary" href="/families">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const setupPhoneCollection = function (targetId) {
        const container = document.getElementById(targetId);
        const template = document.getElementById(targetId + '-template');
        const addButton = document.querySelector('[data-phone-add][data-phone-target="' + targetId + '"]');
        if (!container || !template || !addButton) {
            return;
        }

        const sync = function () {
            const rows = Array.from(container.querySelectorAll('[data-phone-row]'));
            rows.forEach(function (row, index) {
                const number = row.querySelector('[name*="[number]"], [data-phone-number]');
                const label = row.querySelector('[name*="[label]"], [data-phone-label]');
                const radio = row.querySelector('[data-phone-primary]');
                const hidden = row.querySelector('[data-phone-primary-hidden]');
                if (number) {
                    number.name = 'phones[' + index + '][number]';
                }
                if (label) {
                    label.name = 'phones[' + index + '][label]';
                }
                if (radio) {
                    radio.name = 'phone_primary';
                    radio.value = String(index);
                }
                if (hidden) {
                    hidden.name = 'phones[' + index + '][is_primary]';
                    hidden.value = radio && radio.checked ? '1' : '0';
                }
            });

            if (!rows.some(function (row) {
                const radio = row.querySelector('[data-phone-primary]');
                return radio && radio.checked;
            }) && rows[0]) {
                const radio = rows[0].querySelector('[data-phone-primary]');
                if (radio) {
                    radio.checked = true;
                }
            }

            rows.forEach(function (row) {
                const hidden = row.querySelector('[data-phone-primary-hidden]');
                const radio = row.querySelector('[data-phone-primary]');
                if (hidden) {
                    hidden.value = radio && radio.checked ? '1' : '0';
                }
            });
        };

        container.addEventListener('change', function (event) {
            if (event.target.matches('[data-phone-primary]')) {
                sync();
            }
        });

        container.addEventListener('click', function (event) {
            const removeButton = event.target.closest('[data-phone-remove]');
            if (!removeButton) {
                return;
            }
            const row = removeButton.closest('[data-phone-row]');
            if (row) {
                row.remove();
                if (!container.querySelector('[data-phone-row]')) {
                    addButton.click();
                }
                sync();
            }
        });

        addButton.addEventListener('click', function () {
            const fragment = template.content.cloneNode(true);
            container.appendChild(fragment);
            sync();
        });

        sync();
    };

    setupPhoneCollection('family-phones');

    const housingType = document.querySelector('[data-housing-type]');
    const rentGroup = document.querySelector('[data-rent-amount-group]');
    if (housingType && rentGroup) {
        const rentInput = rentGroup.querySelector('input[name="rent_amount"]');
        const syncRent = function () {
            const visible = housingType.value === 'alugada';
            rentGroup.classList.toggle('d-none', !visible);
            if (!visible && rentInput) {
                rentInput.value = '';
            }
        };
        housingType.addEventListener('change', syncRent);
        syncRent();
    }

    document.querySelectorAll('[data-conditional-toggle]').forEach(function (select) {
        const target = select.getAttribute('data-conditional-toggle');
        const group = document.querySelector('[data-conditional-group="' + target + '"]');
        if (!group) {
            return;
        }
        const input = group.querySelector('input, textarea, select');
        const sync = function () {
            const visible = select.value === '1';
            group.classList.toggle('d-none', !visible);
            if (!visible && input) {
                input.value = '';
            }
        };
        select.addEventListener('change', sync);
        sync();
    });
});
</script>

