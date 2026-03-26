<?php
declare(strict_types=1);

$isEdit = ($mode ?? 'create') === 'edit';
$person = is_array($person ?? null) ? $person : [];
$chronicDiseaseOptions = is_array($chronicDiseaseOptions ?? null) ? $chronicDiseaseOptions : [];
$socialBenefitOptions = is_array($socialBenefitOptions ?? null) ? $socialBenefitOptions : [];
?>
<div class="row justify-content-center">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h2 class="h4 mb-1"><?= $isEdit ? 'Editar pessoa acompanhada' : 'Nova pessoa acompanhada' ?></h2>
                        <p class="text-secondary mb-0">Campos incompletos sao permitidos quando nao houver informacao no atendimento.</p>
                    </div>
                    <a class="btn btn-outline-secondary" href="/people">Voltar</a>
                </div>

                <?php if (!empty($error)) : ?>
                    <div class="alert alert-danger border-0 shadow-sm"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>

                <form method="post" action="<?= $isEdit ? '/people/update?id=' . (int) ($person['id'] ?? 0) : '/people' ?>">
                    <div class="row g-3">
                        <div class="col-12"><h3 class="h6 text-uppercase text-secondary mb-0">Identificacao</h3></div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Nome completo</label>
                            <input class="form-control" name="full_name" value="<?= htmlspecialchars((string) ($person['full_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Nome social</label>
                            <input class="form-control" name="social_name" value="<?= htmlspecialchars((string) ($person['social_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label">CPF</label>
                            <input class="form-control" name="cpf" placeholder="opcional" value="<?= htmlspecialchars((string) ($person['cpf'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label">RG</label>
                            <input class="form-control" name="rg" value="<?= htmlspecialchars((string) ($person['rg'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label">Nascimento</label>
                            <input type="date" class="form-control" name="birth_date" value="<?= htmlspecialchars((string) ($person['birth_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label">Idade calculada</label>
                            <input type="text" class="form-control" data-family-age-display readonly tabindex="-1" value="<?= htmlspecialchars((string) (($person['approx_age'] ?? '') !== null ? (string) ($person['approx_age'] ?? '') . ' anos' : ''), ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="approx_age" value="<?= htmlspecialchars((string) (($person['approx_age'] ?? '') !== null ? (string) ($person['approx_age'] ?? '') : ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Genero</label>
                            <input class="form-control" name="gender" value="<?= htmlspecialchars((string) ($person['gender'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>

                        <div class="col-12"><hr><h3 class="h6 text-uppercase text-secondary mb-0">Situacao de rua e vinculos</h3></div>

                        <div class="col-12 col-md-4 d-flex align-items-end">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="is_homeless" name="is_homeless" value="1" <?= ((int) ($person['is_homeless'] ?? 0) === 1) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="is_homeless">Pessoa em situacao de rua</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Tempo em situacao de rua</label>
                            <input class="form-control" name="homeless_time" placeholder="ex.: 3-12m" value="<?= htmlspecialchars((string) ($person['homeless_time'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Local onde permanece</label>
                            <input class="form-control" name="stay_location" value="<?= htmlspecialchars((string) ($person['stay_location'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Numero telefone</label>
                            <input class="form-control" name="phone" value="<?= htmlspecialchars((string) ($person['phone'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="col-12 col-md-8">
                            <label class="form-label">Endereco anterior</label>
                            <input class="form-control" name="previous_address" value="<?= htmlspecialchars((string) ($person['previous_address'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="col-12 col-md-4 d-flex align-items-end">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="has_family_in_region" name="has_family_in_region" value="1" <?= ((int) ($person['has_family_in_region'] ?? 0) === 1) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="has_family_in_region">Tem familia na regiao</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-8">
                            <label class="form-label">Contato/referencia familiar</label>
                            <input class="form-control" name="family_contact" value="<?= htmlspecialchars((string) ($person['family_contact'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>

                        <div class="col-12"><hr><h3 class="h6 text-uppercase text-secondary mb-0">Trabalho e formacao</h3></div>

                        <div class="col-12 col-md-4">
                            <label class="form-label">Escolaridade</label>
                            <input class="form-control" name="education_level" value="<?= htmlspecialchars((string) ($person['education_level'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="col-12 col-md-8">
                            <label class="form-label">Profissao / habilidades</label>
                            <input class="form-control" name="profession_skills" value="<?= htmlspecialchars((string) ($person['profession_skills'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="col-12 col-md-4 d-flex align-items-end">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="formal_work_history" name="formal_work_history" value="1" <?= ((int) ($person['formal_work_history'] ?? 0) === 1) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="formal_work_history">Ja trabalhou com registro</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-4 d-flex align-items-end">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="work_interest" name="work_interest" value="1" <?= ((int) ($person['work_interest'] ?? 0) === 1) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="work_interest">Tem interesse em trabalho</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Detalhe do interesse</label>
                            <input class="form-control" name="work_interest_detail" value="<?= htmlspecialchars((string) ($person['work_interest_detail'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>

                        <div class="col-12"><hr><h3 class="h6 text-uppercase text-secondary mb-0">Saude e beneficios</h3></div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Doenca cronica</label>
                            <select class="form-select" name="chronic_disease">
                                <option value="">Nao informado</option>
                                <?php foreach ($chronicDiseaseOptions as $value => $label) : ?>
                                    <option value="<?= htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8') ?>" <?= ((string) ($person['chronic_disease'] ?? '') === (string) $value) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars((string) $label, ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-4 d-flex align-items-end">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="person_has_physical_disability" name="has_physical_disability" value="1" <?= ((int) ($person['has_physical_disability'] ?? 0) === 1) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="person_has_physical_disability">Possui deficiencia fisica</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Qual deficiencia</label>
                            <input class="form-control" name="physical_disability_details" value="<?= htmlspecialchars((string) ($person['physical_disability_details'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="col-12 col-md-4 d-flex align-items-end">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="person_uses_continuous_medication" name="uses_continuous_medication" value="1" <?= ((int) ($person['uses_continuous_medication'] ?? 0) === 1) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="person_uses_continuous_medication">Faz uso de medicacao continua</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Qual medicacao</label>
                            <input class="form-control" name="continuous_medication_details" value="<?= htmlspecialchars((string) ($person['continuous_medication_details'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Beneficio social</label>
                            <select class="form-select" name="social_benefit">
                                <option value="">Nao informado</option>
                                <?php foreach ($socialBenefitOptions as $value => $label) : ?>
                                    <option value="<?= htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8') ?>" <?= ((string) ($person['social_benefit'] ?? '') === (string) $value) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars((string) $label, ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2 mt-4">
                        <button type="submit" class="btn btn-teal text-white"><?= $isEdit ? 'Salvar alteracoes' : 'Cadastrar pessoa' ?></button>
                        <a class="btn btn-outline-secondary" href="/people">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

