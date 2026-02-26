<?php
declare(strict_types=1);

$isEdit = ($mode ?? 'create') === 'edit';
$familyData = is_array($family ?? null) ? $family : [];
$docStatuses = is_array($docStatuses ?? null) ? $docStatuses : ['ok', 'pendente', 'parcial'];
?>
<div class="row justify-content-center">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h2 class="h4 mb-1"><?= $isEdit ? 'Editar familia' : 'Nova familia' ?></h2>
                        <p class="text-secondary mb-0">Cadastro base com endereco e dados socioeconomicos.</p>
                    </div>
                    <a class="btn btn-outline-secondary" href="/families">Voltar</a>
                </div>

                <?php if (!empty($error)) : ?>
                    <div class="alert alert-danger border-0 shadow-sm"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>

                <form method="post" action="<?= $isEdit ? '/families/update?id=' . (int) ($familyData['id'] ?? 0) : '/families' ?>">
                    <div class="row g-3">
                        <div class="col-12 col-lg-6">
                            <label class="form-label">Responsavel</label>
                            <input class="form-control" name="responsible_name" required value="<?= htmlspecialchars((string) ($familyData['responsible_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <label class="form-label">CPF</label>
                            <input class="form-control" name="cpf_responsible" placeholder="000.000.000-00" value="<?= htmlspecialchars((string) ($familyData['cpf_responsible'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <label class="form-label">RG</label>
                            <input class="form-control" name="rg_responsible" value="<?= htmlspecialchars((string) ($familyData['rg_responsible'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label">Nascimento</label>
                            <input type="date" class="form-control" name="birth_date" value="<?= htmlspecialchars((string) ($familyData['birth_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Telefone</label>
                            <input class="form-control" name="phone" value="<?= htmlspecialchars((string) ($familyData['phone'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Tipo de moradia</label>
                            <input class="form-control" name="housing_type" value="<?= htmlspecialchars((string) ($familyData['housing_type'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>

                        <div class="col-12"><hr><h3 class="h6 text-uppercase text-secondary mb-0">Endereco</h3></div>

                        <div class="col-12 col-md-3">
                            <label class="form-label">CEP</label>
                            <input class="form-control" name="cep" value="<?= htmlspecialchars((string) ($familyData['cep'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
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
                            <input class="form-control" name="marital_status" value="<?= htmlspecialchars((string) ($familyData['marital_status'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Escolaridade</label>
                            <input class="form-control" name="education_level" value="<?= htmlspecialchars((string) ($familyData['education_level'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Situacao profissional</label>
                            <input class="form-control" name="professional_status" value="<?= htmlspecialchars((string) ($familyData['professional_status'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Profissao / detalhe</label>
                            <input class="form-control" name="profession_detail" value="<?= htmlspecialchars((string) ($familyData['profession_detail'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label">Adultos</label>
                            <input type="number" min="0" class="form-control" name="adults_count" value="<?= htmlspecialchars((string) ($familyData['adults_count'] ?? 0), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label">Trabalhadores</label>
                            <input type="number" min="0" class="form-control" name="workers_count" value="<?= htmlspecialchars((string) ($familyData['workers_count'] ?? 0), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label">Criancas</label>
                            <input type="number" min="0" class="form-control" name="children_count" value="<?= htmlspecialchars((string) ($familyData['children_count'] ?? 0), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Renda familiar total</label>
                            <input class="form-control" name="family_income_total" value="<?= htmlspecialchars((string) ($familyData['family_income_total'] ?? '0.00'), ENT_QUOTES, 'UTF-8') ?>">
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
                        <button type="submit" class="btn btn-teal text-white"><?= $isEdit ? 'Salvar alteracoes' : 'Cadastrar familia' ?></button>
                        <a class="btn btn-outline-secondary" href="/families">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

