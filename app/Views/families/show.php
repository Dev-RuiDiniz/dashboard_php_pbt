<?php
declare(strict_types=1);

$family = is_array($family ?? null) ? $family : [];
$members = is_array($members ?? null) ? $members : [];
$children = is_array($children ?? null) ? $children : [];
$memberForm = is_array($memberForm ?? null) ? $memberForm : [];
$memberEditMode = (bool) ($memberEditMode ?? false);
$childForm = is_array($childForm ?? null) ? $childForm : [];
$childEditMode = (bool) ($childEditMode ?? false);
$familyId = (int) ($family['id'] ?? 0);

$personType = (string) ($personType ?? 'member');
$allowedPersonTypes = ['member', 'dependent', 'child'];
if (!in_array($personType, $allowedPersonTypes, true)) {
    $personType = 'member';
}

$openPersonForm = (bool) ($openPersonForm ?? false);
$dependentMode = $personType === 'dependent';
$memberSectionVisible = $personType !== 'child';
$childSectionVisible = $personType === 'child';

$addressLine = implode(' / ', array_filter([
    (string) ($family['neighborhood'] ?? ''),
    (string) ($family['city'] ?? ''),
    (string) ($family['state'] ?? ''),
]));

$memberFormAction = $memberEditMode
    ? '/families/members/update?id=' . (int) ($memberForm['id'] ?? 0) . '&family_id=' . $familyId
    : '/families/members?family_id=' . $familyId;

$childFormAction = $childEditMode
    ? '/families/children/update?id=' . (int) ($childForm['id'] ?? 0) . '&family_id=' . $familyId
    : '/families/children?family_id=' . $familyId;

$memberFormTitle = 'Adicionar membro';
if ($dependentMode) {
    $memberFormTitle = $memberEditMode ? 'Editar dependente' : 'Adicionar dependente';
} elseif ($memberEditMode) {
    $memberFormTitle = 'Editar membro';
}
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
    <form method="post" action="/families/delete?id=<?= $familyId ?>" class="m-0" onsubmit="return confirm('Remover familia? Esta acao exclui membros e criancas vinculados.');">
        <button type="submit" class="btn btn-outline-danger">Remover familia</button>
    </form>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
            <div>
                <h2 class="h5 mb-1">Responsavel principal</h2>
                <p class="text-secondary mb-0">O principal da familia continua aqui. Membro, dependente e crianca sao cadastrados abaixo na mesma aba.</p>
            </div>
            <div class="text-secondary small">Numero da familia: <span class="fw-semibold text-dark">#<?= (int) ($family['id'] ?? 0) ?></span></div>
        </div>
        <hr>
        <div class="row g-3">
            <div class="col-12 col-md-4">
                <div class="small text-secondary text-uppercase">Nome</div>
                <div class="fw-semibold"><?= htmlspecialchars((string) ($family['responsible_name'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div class="col-12 col-md-4">
                <div class="small text-secondary text-uppercase">CPF</div>
                <div><?= htmlspecialchars((string) ($family['cpf_responsible'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div class="col-12 col-md-4">
                <div class="small text-secondary text-uppercase">Telefone</div>
                <div><?= htmlspecialchars((string) ($family['phone'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card metric-card h-100">
            <div class="card-body">
                <div class="small text-secondary text-uppercase">Adultos</div>
                <div class="metric-value"><?= (int) ($family['adults_count'] ?? 0) ?></div>
                <div class="small text-secondary">Calculado por membros</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card metric-card h-100">
            <div class="card-body">
                <div class="small text-secondary text-uppercase">Trabalhadores</div>
                <div class="metric-value"><?= (int) ($family['workers_count'] ?? 0) ?></div>
                <div class="small text-secondary">Membros com trabalho = sim</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card metric-card h-100">
            <div class="card-body">
                <div class="small text-secondary text-uppercase">Renda familiar</div>
                <div class="metric-value">R$ <?= number_format((float) ($family['family_income_total'] ?? 0), 2, ',', '.') ?></div>
                <div class="small text-secondary">Calculada por membros</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card metric-card h-100">
            <div class="card-body">
                <div class="small text-secondary text-uppercase">Criancas</div>
                <div class="metric-value"><?= (int) ($family['children_count'] ?? 0) ?></div>
                <div class="small text-secondary">Cadastro centralizado na familia</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-12 col-xl-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h2 class="h5 mb-3">Resumo da familia</h2>

                <dl class="row mb-0">
                    <dt class="col-5 text-secondary">Endereco</dt>
                    <dd class="col-7"><?= htmlspecialchars($addressLine !== '' ? $addressLine : '-', ENT_QUOTES, 'UTF-8') ?></dd>

                    <dt class="col-5 text-secondary">Documentacao</dt>
                    <dd class="col-7">
                        <span class="badge text-bg-light border"><?= htmlspecialchars((string) ($family['documentation_status'] ?? 'ok'), ENT_QUOTES, 'UTF-8') ?></span>
                    </dd>

                    <dt class="col-5 text-secondary">Necessita visita</dt>
                    <dd class="col-7">
                        <?= ((int) ($family['needs_visit'] ?? 0) === 1)
                            ? '<span class="badge text-bg-warning">Sim</span>'
                            : '<span class="badge text-bg-success">Nao</span>' ?>
                    </dd>

                    <dt class="col-5 text-secondary">Status</dt>
                    <dd class="col-7">
                        <?= ((int) ($family['is_active'] ?? 0) === 1)
                            ? '<span class="badge text-bg-success">Ativo</span>'
                            : '<span class="badge text-bg-danger">Inativo</span>' ?>
                    </dd>
                </dl>

                <?php if (!empty($family['general_notes'])) : ?>
                    <hr>
                    <div class="small text-secondary text-uppercase mb-1">Observacoes</div>
                    <div><?= nl2br(htmlspecialchars((string) $family['general_notes'], ENT_QUOTES, 'UTF-8')) ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-7">
        <div class="card border-0 shadow-sm h-100" data-person-hub>
            <div class="card-body">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
                    <div>
                        <h2 class="h5 mb-1">Cadastro de pessoas da familia</h2>
                        <p class="text-secondary mb-0">Use um unico botao para incluir membro, dependente ou crianca.</p>
                    </div>
                    <button type="button" class="btn btn-teal text-white" data-person-toggle>
                        <?= $openPersonForm ? 'Fechar cadastro' : 'Adicionar pessoa' ?>
                    </button>
                </div>

                <div data-person-panel class="<?= $openPersonForm ? '' : 'd-none' ?>" data-person-open="<?= $openPersonForm ? '1' : '0' ?>">
                    <div class="btn-group mb-3 w-100" role="group" aria-label="Tipo de cadastro">
                        <button type="button" class="btn <?= $personType === 'member' ? 'btn-teal text-white' : 'btn-outline-secondary' ?>" data-person-type-btn="member">Membro</button>
                        <button type="button" class="btn <?= $personType === 'dependent' ? 'btn-teal text-white' : 'btn-outline-secondary' ?>" data-person-type-btn="dependent">Dependente</button>
                        <button type="button" class="btn <?= $personType === 'child' ? 'btn-teal text-white' : 'btn-outline-secondary' ?>" data-person-type-btn="child">Crianca</button>
                    </div>

                    <div data-person-section="member" class="<?= $memberSectionVisible ? '' : 'd-none' ?>" data-member-edit-mode="<?= $memberEditMode ? '1' : '0' ?>">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h3 class="h5 mb-0" data-member-form-title><?= htmlspecialchars($memberFormTitle, ENT_QUOTES, 'UTF-8') ?></h3>
                            <?php if ($memberEditMode) : ?>
                                <a class="btn btn-sm btn-outline-secondary" href="/families/show?id=<?= $familyId ?>&person_type=<?= $dependentMode ? 'dependent' : 'member' ?>">Cancelar edicao</a>
                            <?php endif; ?>
                        </div>

                        <form method="post" action="<?= $memberFormAction ?>">
                            <input type="hidden" name="person_type" value="<?= $dependentMode ? 'dependent' : 'member' ?>" data-member-person-type>

                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Nome</label>
                                    <input class="form-control" name="name" required value="<?= htmlspecialchars((string) ($memberForm['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                </div>
                                <div class="col-12 col-md-6" data-member-relationship-group>
                                    <label class="form-label">Parentesco</label>
                                    <input class="form-control" name="relationship" value="<?= htmlspecialchars((string) ($memberForm['relationship'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                </div>
                                <div class="col-12 col-md-6 <?= $dependentMode ? '' : 'd-none' ?>" data-member-dependent-hint>
                                    <label class="form-label">Classificacao</label>
                                    <input class="form-control" value="Dependente" readonly tabindex="-1">
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label">Nascimento</label>
                                    <input type="date" class="form-control" name="birth_date" value="<?= htmlspecialchars((string) ($memberForm['birth_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label">Renda</label>
                                    <input class="form-control" name="income" value="<?= htmlspecialchars((string) ($memberForm['income'] ?? '0.00'), ENT_QUOTES, 'UTF-8') ?>">
                                </div>
                                <div class="col-12 col-md-4 d-flex align-items-end">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="works" name="works" value="1" <?= ((int) ($memberForm['works'] ?? 0) === 1) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="works">Trabalha</label>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3 d-flex gap-2">
                                <button type="submit" class="btn btn-teal text-white" data-member-submit-label>
                                    <?php if ($dependentMode) : ?>
                                        <?= $memberEditMode ? 'Salvar dependente' : 'Adicionar dependente' ?>
                                    <?php else : ?>
                                        <?= $memberEditMode ? 'Salvar membro' : 'Adicionar membro' ?>
                                    <?php endif; ?>
                                </button>
                                <?php if ($memberEditMode) : ?>
                                    <a class="btn btn-outline-secondary" href="/families/show?id=<?= $familyId ?>&person_type=<?= $dependentMode ? 'dependent' : 'member' ?>">Cancelar</a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>

                    <div data-person-section="child" class="<?= $childSectionVisible ? '' : 'd-none' ?>">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h3 class="h5 mb-0"><?= $childEditMode ? 'Editar crianca' : 'Adicionar crianca' ?></h3>
                            <?php if ($childEditMode) : ?>
                                <a class="btn btn-sm btn-outline-secondary" href="/families/show?id=<?= $familyId ?>&person_type=child">Cancelar edicao</a>
                            <?php endif; ?>
                        </div>

                        <form method="post" action="<?= $childFormAction ?>">
                            <input type="hidden" name="person_type" value="child">
                            <div class="row g-3">
                                <div class="col-12 col-md-4">
                                    <label class="form-label">Nome</label>
                                    <input class="form-control" name="name" required value="<?= htmlspecialchars((string) ($childForm['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                </div>
                                <div class="col-12 col-md-3">
                                    <label class="form-label">Nascimento</label>
                                    <input type="date" class="form-control" name="birth_date" value="<?= htmlspecialchars((string) ($childForm['birth_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                </div>
                                <div class="col-12 col-md-2">
                                    <label class="form-label">Idade aprox.</label>
                                    <input type="number" min="0" class="form-control" name="age_years" value="<?= htmlspecialchars((string) (($childForm['age_years'] ?? '') !== null ? (string) ($childForm['age_years'] ?? '') : ''), ENT_QUOTES, 'UTF-8') ?>">
                                </div>
                                <div class="col-12 col-md-3">
                                    <label class="form-label">Parentesco</label>
                                    <input class="form-control" name="relationship" value="<?= htmlspecialchars((string) ($childForm['relationship'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Observacoes</label>
                                    <input class="form-control" name="notes" value="<?= htmlspecialchars((string) ($childForm['notes'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                </div>
                            </div>

                            <div class="mt-3 d-flex gap-2">
                                <button type="submit" class="btn btn-teal text-white"><?= $childEditMode ? 'Salvar crianca' : 'Adicionar crianca' ?></button>
                                <?php if ($childEditMode) : ?>
                                    <a class="btn btn-outline-secondary" href="/families/show?id=<?= $familyId ?>&person_type=child">Cancelar</a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-12 col-xl-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h3 class="h6 text-uppercase text-secondary mb-3">Membros e dependentes cadastrados</h3>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Tipo</th>
                                <th>Parentesco</th>
                                <th>Trabalha</th>
                                <th>Renda</th>
                                <th>Acoes</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($members)) : ?>
                            <tr>
                                <td colspan="6" class="text-secondary">Nenhum membro cadastrado.</td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($members as $member) : ?>
                                <?php
                                    $memberId = (int) ($member['id'] ?? 0);
                                    $relationship = (string) ($member['relationship'] ?? '');
                                    $rowType = strtolower(trim($relationship)) === 'dependente' ? 'dependent' : 'member';
                                ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold"><?= htmlspecialchars((string) ($member['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                        <div class="small text-secondary"><?= htmlspecialchars((string) ($member['birth_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                    </td>
                                    <td>
                                        <?= $rowType === 'dependent'
                                            ? '<span class="badge text-bg-info">Dependente</span>'
                                            : '<span class="badge text-bg-light border">Membro</span>' ?>
                                    </td>
                                    <td><?= htmlspecialchars($relationship !== '' ? $relationship : '-', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>
                                        <?= ((int) ($member['works'] ?? 0) === 1)
                                            ? '<span class="badge text-bg-success">Sim</span>'
                                            : '<span class="badge text-bg-light border">Nao</span>' ?>
                                    </td>
                                    <td>R$ <?= number_format((float) ($member['income'] ?? 0), 2, ',', '.') ?></td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-2">
                                            <a class="btn btn-sm btn-outline-secondary" href="/families/show?id=<?= $familyId ?>&person_type=<?= $rowType ?>&member_edit=<?= $memberId ?>">Editar</a>
                                            <form method="post" action="/families/members/delete?id=<?= $memberId ?>&family_id=<?= $familyId ?>&person_type=<?= $rowType ?>" class="m-0" onsubmit="return confirm('Remover registro?');">
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

    <div class="col-12 col-xl-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h3 class="h6 text-uppercase text-secondary mb-3">Criancas cadastradas</h3>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Nascimento / Idade</th>
                                <th>Parentesco</th>
                                <th>Acoes</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($children)) : ?>
                            <tr>
                                <td colspan="4" class="text-secondary">Nenhuma crianca vinculada.</td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($children as $child) : ?>
                                <?php $childId = (int) ($child['id'] ?? 0); ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold"><?= htmlspecialchars((string) ($child['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                        <?php if (!empty($child['notes'])) : ?>
                                            <div class="small text-secondary"><?= htmlspecialchars((string) $child['notes'], ENT_QUOTES, 'UTF-8') ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div><?= htmlspecialchars((string) ($child['birth_date'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>
                                        <div class="small text-secondary">idade aprox.: <?= htmlspecialchars((string) (($child['age_years'] ?? '') !== null ? (string) ($child['age_years'] ?? '') : '-'), ENT_QUOTES, 'UTF-8') ?></div>
                                    </td>
                                    <td><?= htmlspecialchars((string) ($child['relationship'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-2">
                                            <a class="btn btn-sm btn-outline-secondary" href="/families/show?id=<?= $familyId ?>&person_type=child&child_edit=<?= $childId ?>">Editar</a>
                                            <form method="post" action="/families/children/delete?id=<?= $childId ?>&family_id=<?= $familyId ?>" class="m-0" onsubmit="return confirm('Remover crianca?');">
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
</div>
