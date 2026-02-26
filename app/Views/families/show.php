<?php
declare(strict_types=1);

$family = is_array($family ?? null) ? $family : [];
$members = is_array($members ?? null) ? $members : [];
$children = is_array($children ?? null) ? $children : [];
$memberForm = is_array($memberForm ?? null) ? $memberForm : [];
$memberEditMode = (bool) ($memberEditMode ?? false);
$familyId = (int) ($family['id'] ?? 0);

$addressLine = implode(' / ', array_filter([
    (string) ($family['neighborhood'] ?? ''),
    (string) ($family['city'] ?? ''),
    (string) ($family['state'] ?? ''),
]));
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
                <div class="small text-secondary text-uppercase">Membros</div>
                <div class="metric-value"><?= count($members) ?></div>
                <div class="small text-secondary">Lista atual da familia</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-12 col-xl-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h2 class="h5 mb-3">Resumo da familia</h2>

                <dl class="row mb-0">
                    <dt class="col-5 text-secondary">Responsavel</dt>
                    <dd class="col-7"><?= htmlspecialchars((string) ($family['responsible_name'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></dd>

                    <dt class="col-5 text-secondary">CPF</dt>
                    <dd class="col-7"><?= htmlspecialchars((string) ($family['cpf_responsible'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></dd>

                    <dt class="col-5 text-secondary">Telefone</dt>
                    <dd class="col-7"><?= htmlspecialchars((string) ($family['phone'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></dd>

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
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="h5 mb-0"><?= $memberEditMode ? 'Editar membro' : 'Adicionar membro' ?></h2>
                    <?php if ($memberEditMode) : ?>
                        <a class="btn btn-sm btn-outline-secondary" href="/families/show?id=<?= $familyId ?>">Cancelar edicao</a>
                    <?php endif; ?>
                </div>

                <form method="post" action="<?= $memberEditMode
                    ? '/families/members/update?id=' . (int) ($memberForm['id'] ?? 0) . '&family_id=' . $familyId
                    : '/families/members?family_id=' . $familyId ?>">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Nome</label>
                            <input class="form-control" name="name" required value="<?= htmlspecialchars((string) ($memberForm['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Parentesco</label>
                            <input class="form-control" name="relationship" value="<?= htmlspecialchars((string) ($memberForm['relationship'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
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
                        <button type="submit" class="btn btn-teal text-white"><?= $memberEditMode ? 'Salvar membro' : 'Adicionar membro' ?></button>
                        <?php if ($memberEditMode) : ?>
                            <a class="btn btn-outline-secondary" href="/families/show?id=<?= $familyId ?>">Cancelar</a>
                        <?php endif; ?>
                    </div>
                </form>

                <hr class="my-4">

                <h3 class="h6 text-uppercase text-secondary mb-3">Membros cadastrados</h3>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Parentesco</th>
                                <th>Trabalha</th>
                                <th>Renda</th>
                                <th>Acoes</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($members)) : ?>
                            <tr>
                                <td colspan="5" class="text-secondary">Nenhum membro cadastrado.</td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($members as $member) : ?>
                                <?php $memberId = (int) ($member['id'] ?? 0); ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold"><?= htmlspecialchars((string) ($member['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                        <div class="small text-secondary"><?= htmlspecialchars((string) ($member['birth_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                    </td>
                                    <td><?= htmlspecialchars((string) ($member['relationship'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>
                                        <?= ((int) ($member['works'] ?? 0) === 1)
                                            ? '<span class="badge text-bg-success">Sim</span>'
                                            : '<span class="badge text-bg-light border">Nao</span>' ?>
                                    </td>
                                    <td>R$ <?= number_format((float) ($member['income'] ?? 0), 2, ',', '.') ?></td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-2">
                                            <a class="btn btn-sm btn-outline-secondary" href="/families/show?id=<?= $familyId ?>&member_edit=<?= $memberId ?>">Editar</a>
                                            <form method="post" action="/families/members/delete?id=<?= $memberId ?>&family_id=<?= $familyId ?>" class="m-0" onsubmit="return confirm('Remover membro?');">
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

<div class="row g-3 mt-1">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
                    <div>
                        <h2 class="h5 mb-1">Criancas vinculadas (aba da familia)</h2>
                        <p class="text-secondary mb-0">Cadastro e consulta de criancas relacionadas a esta familia.</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a class="btn btn-sm btn-outline-secondary" href="/children?family_id=<?= $familyId ?>">Ver lista de criancas</a>
                        <a class="btn btn-sm btn-teal text-white" href="/children/create?family_id=<?= $familyId ?>">Nova crianca</a>
                    </div>
                </div>

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
                                            <a class="btn btn-sm btn-outline-secondary" href="/children/edit?id=<?= $childId ?>">Editar</a>
                                            <form method="post" action="/children/delete?id=<?= $childId ?>&back=family" class="m-0" onsubmit="return confirm('Remover crianca?');">
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
