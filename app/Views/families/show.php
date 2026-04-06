<?php
declare(strict_types=1);

$family = is_array($family ?? null) ? $family : [];
$members = is_array($members ?? null) ? $members : [];
$children = is_array($children ?? null) ? $children : [];
$deliveries = is_array($deliveries ?? null) ? $deliveries : [];
$loans = is_array($loans ?? null) ? $loans : [];
$visits = is_array($visits ?? null) ? $visits : [];
$memberForm = is_array($memberForm ?? null) ? $memberForm : [];
$memberEditMode = (bool) ($memberEditMode ?? false);
$childForm = is_array($childForm ?? null) ? $childForm : [];
$childEditMode = (bool) ($childEditMode ?? false);
$principalForm = is_array($principalForm ?? null) ? $principalForm : [];
$relationshipOptions = is_array($relationshipOptions ?? null) ? $relationshipOptions : [];
$familyId = (int) ($family['id'] ?? 0);
$activeTab = (string) ($activeTab ?? 'composition');
$personType = (string) ($personType ?? 'member');
$auth = is_array($authUser ?? null) ? $authUser : [];
$canDeleteFamily = (string) ($auth['role'] ?? '') === 'admin';
$allowedPersonTypes = ['principal', 'member', 'child'];
if (!in_array($personType, $allowedPersonTypes, true)) {
    $personType = 'member';
}
$familyPhones = is_array($family['phones'] ?? null) ? $family['phones'] : [];
$principalPhones = is_array($principalForm['phones'] ?? null) ? $principalForm['phones'] : [['number' => '', 'label' => '', 'is_primary' => 1]];
$memberFormPersonType = (string) ($memberForm['person_type'] ?? $personType);
if ($memberFormPersonType !== 'member') {
    $memberFormPersonType = $personType === 'member' ? $personType : 'member';
}
$openPersonForm = (bool) ($openPersonForm ?? false);
$principalSectionVisible = $personType === 'principal';
$memberSectionVisible = $personType === 'member';
$childSectionVisible = $personType === 'child';
$addressLine = (string) ($addressLine ?? '');
$principalFormAction = '/families/principal/update?family_id=' . $familyId;
$memberFormAction = $memberEditMode
    ? '/families/members/update?id=' . (int) ($memberForm['id'] ?? 0) . '&family_id=' . $familyId
    : '/families/members?family_id=' . $familyId;
$childFormAction = $childEditMode
    ? '/families/children/update?id=' . (int) ($childForm['id'] ?? 0) . '&family_id=' . $familyId
    : '/families/children?family_id=' . $familyId;

$renderAge = static function ($birthDate): string {
    $value = trim((string) $birthDate);
    if ($value === '' || preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) !== 1) {
        return '-';
    }

    try {
        $birth = new DateTimeImmutable($value);
        $today = new DateTimeImmutable('today');
    } catch (Throwable) {
        return '-';
    }

    if ($birth > $today) {
        return '-';
    }

    return (string) $birth->diff($today)->y . ' anos';
};

$tabUrl = static function (string $tab) use ($familyId): string {
    return '/families/show?' . http_build_query([
        'id' => $familyId,
        'tab' => $tab,
    ]);
};

$personUrl = static function (string $personTypeValue, array $extra = []) use ($familyId): string {
    return '/families/show?' . http_build_query(array_merge([
        'id' => $familyId,
        'tab' => 'composition',
        'person_type' => $personTypeValue,
    ], $extra));
};

$deliveryStatusLabel = static function (string $status): string {
    return match ($status) {
        'retirou' => 'Retirou',
        'presente' => 'Presente',
        default => 'Nao veio',
    };
};

$deliveryStatusClass = static function (string $status): string {
    return match ($status) {
        'retirou' => 'text-bg-success',
        'presente' => 'text-bg-warning',
        default => 'text-bg-light border',
    };
};

$hasDocumentationPending = in_array((string) ($family['documentation_status'] ?? ''), ['pendente', 'parcial'], true)
    || trim((string) ($family['documentation_notes'] ?? '')) !== '';
?>

<?php if (!empty($success)) : ?>
    <div class="alert alert-success shadow-sm border-0"><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>
<?php if (!empty($error)) : ?>
    <div class="alert alert-danger shadow-sm border-0"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<div class="d-flex flex-wrap gap-2 mb-3">
    <a class="btn btn-outline-secondary" href="/families">Voltar para lista</a>
    <a class="btn btn-outline-primary" href="/families/edit?id=<?= $familyId ?>">Editar familia</a>
    <a class="btn btn-outline-success" href="<?= htmlspecialchars($personUrl('principal'), ENT_QUOTES, 'UTF-8') ?>">Abrir composicao</a>
    <?php if ($canDeleteFamily) : ?>
        <form method="post" action="/families/delete?id=<?= $familyId ?>" class="m-0" onsubmit="return confirm('Confirmar exclusao da familia? Esta acao exclui membros e criancas vinculados.');">
            <input type="hidden" name="confirm_delete" value="1">
            <button type="submit" class="btn btn-outline-danger">Remover familia</button>
        </form>
    <?php endif; ?>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <div class="d-flex flex-column flex-xl-row justify-content-between gap-3">
            <div>
                <div class="small text-secondary text-uppercase mb-1">Familia #<?= $familyId ?></div>
                <h2 class="h4 mb-1"><?= htmlspecialchars((string) ($family['responsible_name'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></h2>
                <div class="text-secondary mb-2">
                    CPF <?= htmlspecialchars((string) ($family['cpf_responsible'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                    · Telefone <?= htmlspecialchars((string) (($familyPhones[0]['number'] ?? '') !== '' ? (string) $familyPhones[0]['number'] : '-'), ENT_QUOTES, 'UTF-8') ?>
                </div>
                <?php if ($familyPhones !== []) : ?>
                    <div class="small text-secondary">
                        <?php foreach ($familyPhones as $phone) : ?>
                            <div>
                                <?= htmlspecialchars((string) ($phone['number'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                                <?php if (trim((string) ($phone['label'] ?? '')) !== '') : ?>
                                    · <?= htmlspecialchars((string) $phone['label'], ENT_QUOTES, 'UTF-8') ?>
                                <?php endif; ?>
                                <?php if ((int) ($phone['is_primary'] ?? 0) === 1) : ?>
                                    <span class="badge text-bg-light border">Principal</span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <div class="small text-secondary"><?= htmlspecialchars($addressLine !== '' ? $addressLine : '-', ENT_QUOTES, 'UTF-8') ?></div>
                <div class="small text-secondary mt-2">
                    Cadastro: <?= htmlspecialchars((string) (($family['created_at'] ?? '') ?: '-'), ENT_QUOTES, 'UTF-8') ?>
                    · Ultima atualizacao: <?= htmlspecialchars((string) (($family['updated_at'] ?? '') ?: ($family['created_at'] ?? '-')), ENT_QUOTES, 'UTF-8') ?>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2 align-content-start">
                <span class="badge text-bg-light border">Docs: <?= htmlspecialchars((string) ($family['documentation_status'] ?? 'ok'), ENT_QUOTES, 'UTF-8') ?></span>
                <?php if ($hasDocumentationPending) : ?>
                    <span class="badge text-bg-danger">Documentacao pendente</span>
                <?php else : ?>
                    <span class="badge text-bg-success">Documentacao em dia</span>
                <?php endif; ?>
                <?php if ((int) ($family['needs_visit'] ?? 0) === 1) : ?>
                    <span class="badge text-bg-warning">Visita pendente</span>
                <?php else : ?>
                    <span class="badge text-bg-success">Visita em dia</span>
                <?php endif; ?>
                <?php if ((int) ($family['is_active'] ?? 0) === 1) : ?>
                    <span class="badge text-bg-success">Cadastro ativo</span>
                <?php else : ?>
                    <span class="badge text-bg-danger">Cadastro inativo</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<ul class="nav nav-tabs mb-3">
    <li class="nav-item"><a class="nav-link <?= $activeTab === 'composition' ? 'active' : '' ?>" href="<?= htmlspecialchars($tabUrl('composition'), ENT_QUOTES, 'UTF-8') ?>">Composicao Familiar</a></li>
    <li class="nav-item"><a class="nav-link <?= $activeTab === 'summary' ? 'active' : '' ?>" href="<?= htmlspecialchars($tabUrl('summary'), ENT_QUOTES, 'UTF-8') ?>">Resumo</a></li>
    <li class="nav-item"><a class="nav-link <?= $activeTab === 'deliveries' ? 'active' : '' ?>" href="<?= htmlspecialchars($tabUrl('deliveries'), ENT_QUOTES, 'UTF-8') ?>">Entregas</a></li>
    <li class="nav-item"><a class="nav-link <?= $activeTab === 'loans' ? 'active' : '' ?>" href="<?= htmlspecialchars($tabUrl('loans'), ENT_QUOTES, 'UTF-8') ?>">Emprestimos</a></li>
    <li class="nav-item"><a class="nav-link <?= $activeTab === 'visits' ? 'active' : '' ?>" href="<?= htmlspecialchars($tabUrl('visits'), ENT_QUOTES, 'UTF-8') ?>">Visitas/Anotacoes</a></li>
    <li class="nav-item"><a class="nav-link <?= $activeTab === 'pendencies' ? 'active' : '' ?>" href="<?= htmlspecialchars($tabUrl('pendencies'), ENT_QUOTES, 'UTF-8') ?>">Pendencias</a></li>
</ul>

<?php if ($activeTab === 'composition') : ?>
    <div class="row g-3 mb-3">
        <div class="col-12 col-md-6 col-xl-2">
            <div class="card metric-card h-100"><div class="card-body">
                <div class="small text-secondary text-uppercase">Adultos</div>
                <div class="metric-value"><?= (int) ($family['adults_count'] ?? 0) ?></div>
            </div></div>
        </div>
        <div class="col-12 col-md-6 col-xl-2">
            <div class="card metric-card h-100"><div class="card-body">
                <div class="small text-secondary text-uppercase">Trabalhadores</div>
                <div class="metric-value"><?= (int) ($family['workers_count'] ?? 0) ?></div>
            </div></div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card metric-card h-100"><div class="card-body">
                <div class="small text-secondary text-uppercase">Renda total</div>
                <div class="metric-value">R$ <?= number_format((float) ($family['family_income_total'] ?? 0), 2, ',', '.') ?></div>
            </div></div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card metric-card h-100"><div class="card-body">
                <div class="small text-secondary text-uppercase">Media per capita</div>
                <div class="metric-value">R$ <?= number_format((float) ($family['family_income_average'] ?? 0), 2, ',', '.') ?></div>
            </div></div>
        </div>
        <div class="col-12 col-md-6 col-xl-2">
            <div class="card metric-card h-100"><div class="card-body">
                <div class="small text-secondary text-uppercase">Criancas</div>
                <div class="metric-value"><?= (int) ($family['children_count'] ?? 0) ?></div>
            </div></div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3" data-person-hub>
        <div class="card-body">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
                <div>
                    <h3 class="h5 mb-1">Cadastro de pessoas da familia</h3>
                    <p class="text-secondary mb-0">A primeira aba concentra o fluxo completo de principal, membro da familia e crianca.</p>
                </div>
                <button type="button" class="btn btn-teal text-white" data-person-toggle>
                    <?= $openPersonForm ? 'Fechar cadastro' : 'Adicionar pessoa' ?>
                </button>
            </div>

            <div data-person-panel class="<?= $openPersonForm ? '' : 'd-none' ?>" data-person-open="<?= $openPersonForm ? '1' : '0' ?>">
                <div class="btn-group mb-3 w-100" role="group" aria-label="Tipo de cadastro">
                    <button type="button" class="btn <?= $personType === 'principal' ? 'btn-teal text-white' : 'btn-outline-secondary' ?>" data-person-type-btn="principal">Principal</button>
                    <button type="button" class="btn <?= $personType === 'member' ? 'btn-teal text-white' : 'btn-outline-secondary' ?>" data-person-type-btn="member">Membro</button>
                    <button type="button" class="btn <?= $personType === 'child' ? 'btn-teal text-white' : 'btn-outline-secondary' ?>" data-person-type-btn="child">Crianca</button>
                </div>

                <div data-person-section="principal" class="<?= $principalSectionVisible ? '' : 'd-none' ?>">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3 class="h5 mb-0">Atualizar responsavel principal</h3>
                    </div>

                    <form method="post" action="<?= htmlspecialchars($principalFormAction, ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="person_type" value="principal">
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label">Nome</label>
                                <input class="form-control" name="responsible_name" required value="<?= htmlspecialchars((string) ($principalForm['responsible_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="form-label">CPF</label>
                                <input class="form-control" name="cpf_responsible" required placeholder="000.000.000-00" value="<?= htmlspecialchars((string) ($principalForm['cpf_responsible'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="form-label">RG</label>
                                <input class="form-control" name="rg_responsible" placeholder="opcional" value="<?= htmlspecialchars((string) ($principalForm['rg_responsible'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">Nascimento</label>
                                <input type="date" class="form-control" name="birth_date" value="<?= htmlspecialchars((string) ($principalForm['birth_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                            <div class="col-12 col-md-2">
                                <label class="form-label">Idade</label>
                                <input type="text" class="form-control" data-family-age-display readonly tabindex="-1" placeholder="Automatica">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label d-flex justify-content-between align-items-center">
                                    <span>Telefones</span>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-phone-add data-phone-target="principal-phones">Adicionar telefone</button>
                                </label>
                                <div class="vstack gap-2" id="principal-phones">
                                    <?php foreach ($principalPhones as $index => $phoneEntry) : ?>
                                        <div class="border rounded p-2" data-phone-row>
                                            <div class="row g-2 align-items-end">
                                                <div class="col-12 col-md-5">
                                                    <label class="form-label small">Numero</label>
                                                    <input class="form-control" name="phones[<?= $index ?>][number]" value="<?= htmlspecialchars((string) ($phoneEntry['number'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                                </div>
                                                <div class="col-12 col-md-5">
                                                    <label class="form-label small">Identificacao / observacao</label>
                                                    <input class="form-control" name="phones[<?= $index ?>][label]" value="<?= htmlspecialchars((string) ($phoneEntry['label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
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
                                <template id="principal-phones-template">
                                    <div class="border rounded p-2" data-phone-row>
                                        <div class="row g-2 align-items-end">
                                            <div class="col-12 col-md-5">
                                                <label class="form-label small">Numero</label>
                                                <input class="form-control" data-phone-number>
                                            </div>
                                            <div class="col-12 col-md-5">
                                                <label class="form-label small">Identificacao / observacao</label>
                                                <input class="form-control" data-phone-label>
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
                                <label class="form-label">Renda</label>
                                <input class="form-control" name="responsible_income" value="<?= htmlspecialchars((string) ($principalForm['responsible_income'] ?? '0.00'), ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                            <div class="col-12 col-md-4 d-flex align-items-end">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="responsible_works" name="responsible_works" value="1" <?= ((int) ($principalForm['responsible_works'] ?? 0) === 1) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="responsible_works">Trabalha</label>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 d-flex gap-2">
                            <button type="submit" class="btn btn-teal text-white">Salvar principal</button>
                        </div>
                    </form>
                </div>

                <div data-person-section="member" class="<?= $memberSectionVisible ? '' : 'd-none' ?>" data-member-edit-mode="<?= $memberEditMode ? '1' : '0' ?>">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3 class="h5 mb-0" data-member-form-title><?= $memberEditMode ? 'Editar membro familiar' : 'Adicionar membro familiar' ?></h3>
                        <?php if ($memberEditMode) : ?>
                            <a class="btn btn-sm btn-outline-secondary" href="<?= htmlspecialchars($personUrl($memberFormPersonType), ENT_QUOTES, 'UTF-8') ?>">Cancelar edicao</a>
                        <?php endif; ?>
                    </div>

                    <form method="post" action="<?= htmlspecialchars($memberFormAction, ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="person_type" value="<?= htmlspecialchars($memberFormPersonType, ENT_QUOTES, 'UTF-8') ?>" data-member-person-type>
                        <div class="row g-3">
                            <div class="col-12 col-md-5">
                                <label class="form-label">Nome</label>
                                <input class="form-control" name="name" required value="<?= htmlspecialchars((string) ($memberForm['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="form-label">CPF</label>
                                <input class="form-control" name="cpf" required placeholder="000.000.000-00" value="<?= htmlspecialchars((string) ($memberForm['cpf'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                            <div class="col-12 col-md-2">
                                <label class="form-label">RG</label>
                                <input class="form-control" name="rg" placeholder="opcional" value="<?= htmlspecialchars((string) ($memberForm['rg'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                            <div class="col-12 col-md-2" data-member-relationship-group>
                                <label class="form-label">Parentesco</label>
                                <select class="form-select" name="relationship">
                                    <option value="">Selecione</option>
                                    <?php foreach ($relationshipOptions as $relationshipOption) : ?>
                                        <option value="<?= htmlspecialchars($relationshipOption, ENT_QUOTES, 'UTF-8') ?>" <?= ((string) ($memberForm['relationship'] ?? '') === $relationshipOption) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($relationshipOption, ENT_QUOTES, 'UTF-8') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="form-label">Nascimento</label>
                                <input type="date" class="form-control" name="birth_date" value="<?= htmlspecialchars((string) ($memberForm['birth_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                            <div class="col-12 col-md-2">
                                <label class="form-label">Idade</label>
                                <input type="text" class="form-control" data-family-age-display readonly tabindex="-1" placeholder="Automatica">
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="form-label">Renda</label>
                                <input class="form-control" name="income" value="<?= htmlspecialchars((string) ($memberForm['income'] ?? '0.00'), ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                            <div class="col-12 col-md-3 d-flex align-items-end">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="member_social_benefit" name="receives_social_benefit" value="1" <?= ((int) ($memberForm['receives_social_benefit'] ?? 0) === 1) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="member_social_benefit">Recebe beneficio social</label>
                                </div>
                            </div>
                            <div class="col-12 col-md-3 d-flex align-items-end">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="member_studies" name="studies" value="1" <?= ((int) ($memberForm['studies'] ?? 0) === 1) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="member_studies">Estuda</label>
                                </div>
                            </div>
                            <div class="col-12 col-md-3 d-flex align-items-end" data-member-works-group>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="works" name="works" value="1" <?= ((int) ($memberForm['works'] ?? 0) === 1) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="works">Trabalha</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Objetivo</label>
                                <input class="form-control" name="purpose" value="<?= htmlspecialchars((string) ($memberForm['purpose'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                            <div class="col-12">
                                <div class="form-text">Para maiores de idade: informe se estuda e se trabalha. Para menores, apenas estudo.</div>
                            </div>
                        </div>

                        <div class="mt-3 d-flex gap-2">
                            <button type="submit" class="btn btn-teal text-white" data-member-submit-label><?= $memberEditMode ? 'Salvar membro' : 'Adicionar membro' ?></button>
                            <?php if ($memberEditMode) : ?>
                                <a class="btn btn-outline-secondary" href="<?= htmlspecialchars($personUrl($memberFormPersonType), ENT_QUOTES, 'UTF-8') ?>">Cancelar</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <div data-person-section="child" class="<?= $childSectionVisible ? '' : 'd-none' ?>">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3 class="h5 mb-0"><?= $childEditMode ? 'Editar crianca' : 'Adicionar crianca' ?></h3>
                        <?php if ($childEditMode) : ?>
                            <a class="btn btn-sm btn-outline-secondary" href="<?= htmlspecialchars($personUrl('child'), ENT_QUOTES, 'UTF-8') ?>">Cancelar edicao</a>
                        <?php endif; ?>
                    </div>

                    <form method="post" action="<?= htmlspecialchars($childFormAction, ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="person_type" value="child">
                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <label class="form-label">Nome</label>
                                <input class="form-control" name="name" required value="<?= htmlspecialchars((string) ($childForm['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="form-label">CPF (opcional)</label>
                                <input class="form-control" name="cpf" placeholder="000.000.000-00" value="<?= htmlspecialchars((string) ($childForm['cpf'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                            <div class="col-12 col-md-2">
                                <label class="form-label">RG (opcional)</label>
                                <input class="form-control" name="rg" placeholder="00.000.000-0" value="<?= htmlspecialchars((string) ($childForm['rg'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="form-label">Nascimento</label>
                                <input type="date" class="form-control" name="birth_date" value="<?= htmlspecialchars((string) ($childForm['birth_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                            <div class="col-12 col-md-2">
                                <label class="form-label">Idade</label>
                                <input type="text" class="form-control" data-family-age-display readonly tabindex="-1" placeholder="Automatica">
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="form-label">Parentesco</label>
                                <select class="form-select" name="relationship">
                                    <option value="">Selecione</option>
                                    <?php foreach ($relationshipOptions as $relationshipOption) : ?>
                                        <option value="<?= htmlspecialchars($relationshipOption, ENT_QUOTES, 'UTF-8') ?>" <?= ((string) ($childForm['relationship'] ?? '') === $relationshipOption) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($relationshipOption, ENT_QUOTES, 'UTF-8') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12 col-md-3 d-flex align-items-end">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="child_studies" name="studies" value="1" <?= ((int) ($childForm['studies'] ?? 0) === 1) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="child_studies">Estuda</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Observacoes</label>
                                <input class="form-control" name="notes" value="<?= htmlspecialchars((string) ($childForm['notes'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                        </div>

                        <div class="mt-3 d-flex gap-2">
                            <button type="submit" class="btn btn-teal text-white"><?= $childEditMode ? 'Salvar crianca' : 'Adicionar crianca' ?></button>
                            <?php if ($childEditMode) : ?>
                                <a class="btn btn-outline-secondary" href="<?= htmlspecialchars($personUrl('child'), ENT_QUOTES, 'UTF-8') ?>">Cancelar</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php if ($activeTab === 'composition') : ?>
    <div class="row g-3">
        <div class="col-12 col-xl-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h3 class="h6 text-uppercase text-secondary mb-3">Membros da familia cadastrados</h3>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Tipo</th>
                                    <th>Nome</th>
                                    <th>Documentos</th>
                                    <th>Estuda</th>
                                    <th>Trabalha</th>
                                    <th>Renda</th>
                                    <th>Acoes</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (empty($members)) : ?>
                                <tr>
                                    <td colspan="7" class="text-secondary">Nenhum registro cadastrado.</td>
                                </tr>
                            <?php else : ?>
                                <?php foreach ($members as $member) : ?>
                                    <?php $memberId = (int) ($member['id'] ?? 0); ?>
                                    <tr>
                                        <td><span class="badge text-bg-light border"><?= htmlspecialchars((string) ($member['type_label'] ?? 'Membro'), ENT_QUOTES, 'UTF-8') ?></span></td>
                                        <td>
                                            <div class="fw-semibold"><?= htmlspecialchars((string) ($member['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                            <div class="small text-secondary"><?= htmlspecialchars((string) ($member['relationship'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>
                                            <div class="small text-secondary">idade: <?= htmlspecialchars($renderAge($member['birth_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                            <div class="small text-secondary">beneficio social: <?= ((int) ($member['receives_social_benefit'] ?? 0) === 1) ? 'Sim' : 'Nao' ?></div>
                                            <?php if (trim((string) ($member['purpose'] ?? '')) !== '') : ?>
                                                <div class="small text-secondary">objetivo: <?= htmlspecialchars((string) $member['purpose'], ENT_QUOTES, 'UTF-8') ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="small">
                                            <div>CPF: <?= htmlspecialchars((string) ($member['cpf'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>
                                            <div>RG: <?= htmlspecialchars((string) ($member['rg'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>
                                        </td>
                                        <td>
                                            <?= ((int) ($member['studies'] ?? 0) === 1)
                                                ? '<span class="badge text-bg-success">Sim</span>'
                                                : '<span class="badge text-bg-light border">Nao</span>' ?>
                                        </td>
                                        <td>
                                            <?= ((int) ($member['works'] ?? 0) === 1)
                                                ? '<span class="badge text-bg-success">Sim</span>'
                                                : '<span class="badge text-bg-light border">Nao</span>' ?>
                                        </td>
                                        <td>R$ <?= number_format((float) ($member['income'] ?? 0), 2, ',', '.') ?></td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-2">
                                                <a class="btn btn-sm btn-outline-secondary" href="<?= htmlspecialchars($personUrl((string) ($member['person_type'] ?? 'member'), ['member_edit' => $memberId]), ENT_QUOTES, 'UTF-8') ?>">Editar</a>
                                                <form method="post" action="/families/members/delete?id=<?= $memberId ?>&family_id=<?= $familyId ?>&person_type=<?= htmlspecialchars((string) ($member['person_type'] ?? 'member'), ENT_QUOTES, 'UTF-8') ?>" class="m-0" onsubmit="return confirm('Remover registro?');">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Remover</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h3 class="h6 text-uppercase text-secondary mb-3">Criancas cadastradas</h3>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Nascimento / Idade</th>
                                    <th>Estuda</th>
                                    <th>Documentos</th>
                                    <th>Acoes</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (empty($children)) : ?>
                                <tr>
                                    <td colspan="5" class="text-secondary">Nenhuma crianca vinculada.</td>
                                </tr>
                            <?php else : ?>
                                <?php foreach ($children as $child) : ?>
                                    <?php $childId = (int) ($child['id'] ?? 0); ?>
                                    <tr>
                                        <td>
                                            <div class="fw-semibold"><?= htmlspecialchars((string) ($child['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                            <div class="small text-secondary"><?= htmlspecialchars((string) ($child['relationship'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>
                                        </td>
                                        <td>
                                            <div><?= htmlspecialchars((string) ($child['birth_date'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>
                                            <div class="small text-secondary">idade: <?= htmlspecialchars(((string) ($child['age_years'] ?? '')) !== '' ? ((string) $child['age_years'] . ' anos') : $renderAge($child['birth_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                        </td>
                                        <td>
                                            <?= ((int) ($child['studies'] ?? 0) === 1)
                                                ? '<span class="badge text-bg-success">Sim</span>'
                                                : '<span class="badge text-bg-light border">Nao</span>' ?>
                                        </td>
                                        <td class="small">
                                            <div>CPF: <?= htmlspecialchars((string) ($child['cpf'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>
                                            <div>RG: <?= htmlspecialchars((string) ($child['rg'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-2">
                                                <a class="btn btn-sm btn-outline-secondary" href="<?= htmlspecialchars($personUrl('child', ['child_edit' => $childId]), ENT_QUOTES, 'UTF-8') ?>">Editar</a>
                                                <form method="post" action="/families/children/delete?id=<?= $childId ?>&family_id=<?= $familyId ?>" class="m-0" onsubmit="return confirm('Remover crianca?');">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Remover</button>
                                                </form>
                                            </div>
                                            <div class="small text-secondary mt-1">Se o cadastro estiver errado como crianca, remova e recadastre em Membro.</div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php elseif ($activeTab === 'summary') : ?>
    <div class="row g-3">
        <div class="col-12 col-xl-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h3 class="h5 mb-3">Resumo operacional da familia</h3>
                    <dl class="row mb-0">
                        <dt class="col-5 text-secondary">Endereco</dt>
                        <dd class="col-7"><?= htmlspecialchars($addressLine !== '' ? $addressLine : '-', ENT_QUOTES, 'UTF-8') ?></dd>
                        <dt class="col-5 text-secondary">Telefones</dt>
                        <dd class="col-7">
                            <?php if ($familyPhones === []) : ?>
                                -
                            <?php else : ?>
                                <?php foreach ($familyPhones as $phone) : ?>
                                    <div>
                                        <?= htmlspecialchars((string) ($phone['number'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                                        <?php if (trim((string) ($phone['label'] ?? '')) !== '') : ?>
                                            · <?= htmlspecialchars((string) $phone['label'], ENT_QUOTES, 'UTF-8') ?>
                                        <?php endif; ?>
                                        <?php if ((int) ($phone['is_primary'] ?? 0) === 1) : ?>
                                            <span class="badge text-bg-light border">Principal</span>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </dd>
                        <dt class="col-5 text-secondary">Nascimento principal</dt>
                        <dd class="col-7"><?= htmlspecialchars((string) ($family['birth_date'] ?? '-'), ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($renderAge($family['birth_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?>)</dd>
                        <dt class="col-5 text-secondary">Moradia</dt>
                        <dd class="col-7">
                            <?= htmlspecialchars((string) (($family['housing_type'] ?? '') !== '' ? $family['housing_type'] : '-'), ENT_QUOTES, 'UTF-8') ?>
                            <?php if ((string) ($family['housing_type'] ?? '') === 'outro' && trim((string) ($family['housing_type_other_details'] ?? '')) !== '') : ?>
                                · <?= htmlspecialchars((string) $family['housing_type_other_details'], ENT_QUOTES, 'UTF-8') ?>
                            <?php endif; ?>
                        </dd>
                        <?php if ((string) ($family['housing_type'] ?? '') === 'alugada') : ?>
                            <dt class="col-5 text-secondary">Valor do aluguel</dt>
                            <dd class="col-7">R$ <?= number_format((float) ($family['rent_amount'] ?? 0), 2, ',', '.') ?></dd>
                        <?php endif; ?>
                        <dt class="col-5 text-secondary">Estado civil</dt>
                        <dd class="col-7"><?= htmlspecialchars((string) (($family['marital_status'] ?? '') !== '' ? $family['marital_status'] : '-'), ENT_QUOTES, 'UTF-8') ?></dd>
                        <dt class="col-5 text-secondary">Escolaridade</dt>
                        <dd class="col-7"><?= htmlspecialchars((string) (($family['education_level'] ?? '') !== '' ? $family['education_level'] : '-'), ENT_QUOTES, 'UTF-8') ?></dd>
                        <dt class="col-5 text-secondary">Situacao profissional</dt>
                        <dd class="col-7"><?= htmlspecialchars((string) (($family['professional_status'] ?? '') !== '' ? $family['professional_status'] : '-'), ENT_QUOTES, 'UTF-8') ?></dd>
                        <dt class="col-5 text-secondary">Renda principal</dt>
                        <dd class="col-7">R$ <?= number_format((float) ($family['responsible_income'] ?? 0), 2, ',', '.') ?> / <?= ((int) ($family['responsible_works'] ?? 0) === 1) ? 'Trabalha' : 'Nao trabalha' ?></dd>
                        <dt class="col-5 text-secondary">Doenca cronica</dt>
                        <dd class="col-7">
                            <?= htmlspecialchars(!empty($family['chronic_disease_labels']) ? implode(', ', $family['chronic_disease_labels']) : '-', ENT_QUOTES, 'UTF-8') ?>
                        </dd>
                        <dt class="col-5 text-secondary">Deficiencia fisica</dt>
                        <dd class="col-7">
                            <?= ((int) ($family['has_physical_disability'] ?? 0) === 1)
                                ? htmlspecialchars((string) (($family['physical_disability_details'] ?? '') !== '' ? $family['physical_disability_details'] : 'Sim'), ENT_QUOTES, 'UTF-8')
                                : 'Nao' ?>
                        </dd>
                        <dt class="col-5 text-secondary">Medicacao continua</dt>
                        <dd class="col-7">
                            <?= ((int) ($family['uses_continuous_medication'] ?? 0) === 1)
                                ? htmlspecialchars((string) (($family['continuous_medication_details'] ?? '') !== '' ? $family['continuous_medication_details'] : 'Sim'), ENT_QUOTES, 'UTF-8')
                                : 'Nao' ?>
                        </dd>
                        <dt class="col-5 text-secondary">Vicio</dt>
                        <dd class="col-7">
                            <?= ((int) ($family['has_addiction'] ?? 0) === 1)
                                ? htmlspecialchars((string) (($family['addiction_details'] ?? '') !== '' ? $family['addiction_details'] : 'Sim'), ENT_QUOTES, 'UTF-8')
                                : 'Nao' ?>
                        </dd>
                        <dt class="col-5 text-secondary">Beneficio social</dt>
                        <dd class="col-7"><?= htmlspecialchars((string) (($family['social_benefit'] ?? '') !== '' ? $family['social_benefit'] : '-'), ENT_QUOTES, 'UTF-8') ?></dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h3 class="h5 mb-3">Indicadores consolidados</h3>
                    <div class="row g-3">
                        <div class="col-6"><div class="border rounded p-3"><div class="small text-secondary">Adultos</div><div class="h4 mb-0"><?= (int) ($family['adults_count'] ?? 0) ?></div></div></div>
                        <div class="col-6"><div class="border rounded p-3"><div class="small text-secondary">Trabalhadores</div><div class="h4 mb-0"><?= (int) ($family['workers_count'] ?? 0) ?></div></div></div>
                        <div class="col-12"><div class="border rounded p-3"><div class="small text-secondary">Renda familiar total</div><div class="h4 mb-0">R$ <?= number_format((float) ($family['family_income_total'] ?? 0), 2, ',', '.') ?></div></div></div>
                        <div class="col-12"><div class="border rounded p-3"><div class="small text-secondary">Media de renda per capita</div><div class="h4 mb-0">R$ <?= number_format((float) ($family['family_income_average'] ?? 0), 2, ',', '.') ?></div></div></div>
                        <div class="col-12"><div class="border rounded p-3"><div class="small text-secondary">Criancas</div><div class="h4 mb-0"><?= (int) ($family['children_count'] ?? 0) ?></div></div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php if ($activeTab === 'deliveries') : ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between gap-2 mb-3">
                <div>
                    <h3 class="h5 mb-1">Historico de entregas da familia</h3>
                    <p class="text-secondary mb-0">Lista operacional por evento com acesso rapido para preselecionar a familia.</p>
                </div>
                <a class="btn btn-outline-primary" href="/delivery-events">Selecionar evento</a>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Evento</th>
                            <th>Senha</th>
                            <th>Status</th>
                            <th>Quantidade</th>
                            <th>Retirada</th>
                            <th>Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($deliveries)) : ?>
                        <tr>
                            <td colspan="6" class="text-secondary">Nenhuma entrega encontrada para esta familia.</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($deliveries as $delivery) : ?>
                            <?php
                            $eventId = (int) ($delivery['event_id'] ?? 0);
                            $eventStatus = (string) ($delivery['event_status'] ?? '');
                            ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?= htmlspecialchars((string) ($delivery['event_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                    <div class="small text-secondary"><?= htmlspecialchars((string) ($delivery['event_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                </td>
                                <td>#<?= (int) ($delivery['ticket_number'] ?? 0) ?></td>
                                <td><span class="badge <?= htmlspecialchars($deliveryStatusClass((string) ($delivery['status'] ?? 'nao_veio')), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($deliveryStatusLabel((string) ($delivery['status'] ?? 'nao_veio')), ENT_QUOTES, 'UTF-8') ?></span></td>
                                <td><?= (int) ($delivery['quantity'] ?? 0) ?></td>
                                <td><?= htmlspecialchars((string) ($delivery['delivered_at'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <div class="d-flex flex-wrap gap-2">
                                        <a class="btn btn-sm btn-outline-secondary" href="/delivery-events/show?id=<?= $eventId ?>">Abrir evento</a>
                                        <?php if ($eventStatus !== 'concluido') : ?>
                                            <a class="btn btn-sm btn-outline-primary" href="/delivery-events/show?id=<?= $eventId ?>&family_id=<?= $familyId ?>">Preselecionar familia</a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php elseif ($activeTab === 'loans') : ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between gap-2 mb-3">
                <div>
                    <h3 class="h5 mb-1">Historico de emprestimos</h3>
                    <p class="text-secondary mb-0">Controle de equipamentos associados a esta familia.</p>
                </div>
                <a class="btn btn-outline-primary" href="/equipment-loans?family_id=<?= $familyId ?>">Novo emprestimo para esta familia</a>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Equipamento</th>
                            <th>Emprestimo</th>
                            <th>Previsao</th>
                            <th>Devolucao</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($loans)) : ?>
                        <tr>
                            <td colspan="5" class="text-secondary">Nenhum emprestimo encontrado para esta familia.</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($loans as $loan) : ?>
                            <?php
                            $isReturned = !empty($loan['return_date']);
                            $isOverdue = !$isReturned && !empty($loan['due_date']) && (string) $loan['due_date'] < date('Y-m-d');
                            ?>
                            <tr class="<?= $isOverdue ? 'table-danger' : '' ?>">
                                <td>
                                    <div class="fw-semibold"><?= htmlspecialchars((string) ($loan['equipment_code'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                    <div class="small text-secondary"><?= htmlspecialchars((string) ($loan['equipment_type'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                </td>
                                <td><?= htmlspecialchars((string) ($loan['loan_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) ($loan['due_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) ($loan['return_date'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <?php if ($isReturned) : ?>
                                        <span class="badge text-bg-success">Devolvido</span>
                                    <?php elseif ($isOverdue) : ?>
                                        <span class="badge text-bg-danger">Atrasado</span>
                                    <?php else : ?>
                                        <span class="badge text-bg-warning">Aberto</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

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
            container.appendChild(template.content.cloneNode(true));
            sync();
        });

        sync();
    };

    setupPhoneCollection('principal-phones');
});
</script>

<?php if ($activeTab === 'visits') : ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between gap-2 mb-3">
                <div>
                    <h3 class="h5 mb-1">Visitas e anotacoes</h3>
                    <p class="text-secondary mb-0">Solicite visitas com a familia ja selecionada e acompanhe o historico.</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a class="btn btn-outline-primary" href="/visits/create?family_id=<?= $familyId ?>">Solicitar visita</a>
                    <a class="btn btn-outline-secondary" href="/visits">Abrir modulo de visitas</a>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Solicitada</th>
                            <th>Agendada</th>
                            <th>Status</th>
                            <th>Observacoes</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($visits)) : ?>
                        <tr>
                            <td colspan="4" class="text-secondary">Nenhuma visita registrada para esta familia.</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($visits as $visit) : ?>
                            <tr>
                                <td>
                                    <div><?= htmlspecialchars((string) ($visit['requested_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                    <div class="small text-secondary"><?= htmlspecialchars((string) ($visit['requested_by_name'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>
                                </td>
                                <td><?= htmlspecialchars((string) ($visit['scheduled_date'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><span class="badge text-bg-light border"><?= htmlspecialchars((string) ($visit['status'] ?? 'pendente'), ENT_QUOTES, 'UTF-8') ?></span></td>
                                <td><?= htmlspecialchars((string) ($visit['notes'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if (!empty($family['general_notes'])) : ?>
                <hr>
                <div class="small text-secondary text-uppercase mb-1">Observacoes gerais da familia</div>
                <div><?= nl2br(htmlspecialchars((string) $family['general_notes'], ENT_QUOTES, 'UTF-8')) ?></div>
            <?php endif; ?>
        </div>
    </div>
<?php elseif ($activeTab === 'pendencies') : ?>
    <div class="row g-3">
        <div class="col-12 col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h3 class="h5 mb-3">Pendencias operacionais</h3>
                    <div class="mb-3">
                        <div class="small text-secondary text-uppercase">Documentacao</div>
                        <div class="fw-semibold"><?= htmlspecialchars((string) ($family['documentation_status'] ?? 'ok'), ENT_QUOTES, 'UTF-8') ?></div>
                    </div>
                    <div class="mb-3">
                        <div class="small text-secondary text-uppercase">Alerta documental</div>
                        <div class="fw-semibold"><?= $hasDocumentationPending ? 'Documentacao pendente' : 'Sem pendencias' ?></div>
                    </div>
                    <div class="mb-3">
                        <div class="small text-secondary text-uppercase">Necessita visita</div>
                        <div class="fw-semibold"><?= ((int) ($family['needs_visit'] ?? 0) === 1) ? 'Sim' : 'Nao' ?></div>
                    </div>
                    <div class="mb-3">
                        <div class="small text-secondary text-uppercase">Status do cadastro</div>
                        <div class="fw-semibold"><?= ((int) ($family['is_active'] ?? 0) === 1) ? 'Ativo' : 'Inativo' ?></div>
                    </div>
                    <div>
                        <div class="small text-secondary text-uppercase">Ultima atualizacao</div>
                        <div class="fw-semibold"><?= htmlspecialchars((string) ($family['updated_at'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h3 class="h5 mb-3">Observacoes e anotacoes</h3>
                    <div class="mb-4">
                        <div class="small text-secondary text-uppercase mb-1">Pendencias de documentacao</div>
                        <div><?= nl2br(htmlspecialchars((string) (($family['documentation_notes'] ?? '') !== '' ? $family['documentation_notes'] : 'Nenhuma pendencia registrada.'), ENT_QUOTES, 'UTF-8')) ?></div>
                    </div>
                    <div class="mb-4">
                        <div class="small text-secondary text-uppercase mb-1">Observacoes gerais</div>
                        <div><?= nl2br(htmlspecialchars((string) (($family['general_notes'] ?? '') !== '' ? $family['general_notes'] : 'Nenhuma observacao registrada.'), ENT_QUOTES, 'UTF-8')) ?></div>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <a class="btn btn-outline-primary" href="/families/edit?id=<?= $familyId ?>">Editar dados da familia</a>
                        <a class="btn btn-outline-secondary" href="<?= htmlspecialchars($tabUrl('summary'), ENT_QUOTES, 'UTF-8') ?>">Ver resumo consolidado</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
