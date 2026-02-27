<?php
declare(strict_types=1);

$filters = is_array($filters ?? null) ? $filters : [];
$loanForm = is_array($loanForm ?? null) ? $loanForm : [];
$availableEquipment = is_array($availableEquipment ?? null) ? $availableEquipment : [];
$families = is_array($families ?? null) ? $families : [];
$people = is_array($people ?? null) ? $people : [];
$returnConditions = is_array($returnConditions ?? null) ? $returnConditions : [];
$overdueLoans = is_array($overdueLoans ?? null) ? $overdueLoans : [];
?>
<?php if (!empty($success)) : ?>
    <div class="alert alert-success shadow-sm border-0"><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>
<?php if (!empty($error)) : ?>
    <div class="alert alert-danger shadow-sm border-0"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<?php if ((int) ($overdueCount ?? 0) > 0) : ?>
    <div class="alert alert-danger shadow-sm border-0">
        <div class="fw-semibold mb-2">Alertas de devolucao atrasada: <?= (int) $overdueCount ?></div>
        <div class="small">
            <?php foreach ($overdueLoans as $idx => $loan) : ?>
                <?php if ($idx > 0) : ?> | <?php endif; ?>
                <?= htmlspecialchars((string) ($loan['equipment_code'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                (venc.: <?= htmlspecialchars((string) ($loan['due_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?>)
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between gap-2 mb-3">
            <div>
                <h2 class="h5 mb-1">Novo emprestimo</h2>
                <p class="text-secondary mb-0">Ao emprestar, o equipamento passa automaticamente para status emprestado.</p>
            </div>
            <a class="btn btn-outline-secondary" href="/equipment">Voltar ao estoque</a>
        </div>

        <form method="post" action="/equipment-loans" class="row g-3">
            <div class="col-12 col-lg-4">
                <label class="form-label">Equipamento disponivel</label>
                <select class="form-select" name="equipment_id" required>
                    <option value="0">Selecione</option>
                    <?php foreach ($availableEquipment as $eq) : ?>
                        <?php $eqId = (int) ($eq['id'] ?? 0); ?>
                        <option value="<?= $eqId ?>" <?= ((int) ($loanForm['equipment_id'] ?? 0) === $eqId) ? 'selected' : '' ?>>
                            <?= htmlspecialchars((string) ($eq['code'] ?? ''), ENT_QUOTES, 'UTF-8') ?> - <?= htmlspecialchars(ucwords(str_replace('_', ' ', (string) ($eq['type'] ?? ''))), ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-lg-2">
                <label class="form-label">Destino</label>
                <select class="form-select" name="target_type">
                    <option value="family" <?= ((string) ($loanForm['target_type'] ?? 'family') === 'family') ? 'selected' : '' ?>>Familia</option>
                    <option value="person" <?= ((string) ($loanForm['target_type'] ?? 'family') === 'person') ? 'selected' : '' ?>>Pessoa</option>
                </select>
            </div>
            <div class="col-12 col-lg-3">
                <label class="form-label">Familia</label>
                <select class="form-select" name="family_id">
                    <option value="0">Selecione</option>
                    <?php foreach ($families as $family) : ?>
                        <?php $familyId = (int) ($family['id'] ?? 0); ?>
                        <option value="<?= $familyId ?>" <?= ((int) ($loanForm['family_id'] ?? 0) === $familyId) ? 'selected' : '' ?>>
                            <?= htmlspecialchars((string) ($family['responsible_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-lg-3">
                <label class="form-label">Pessoa</label>
                <select class="form-select" name="person_id">
                    <option value="0">Selecione</option>
                    <?php foreach ($people as $person) : ?>
                        <?php $personId = (int) ($person['id'] ?? 0); ?>
                        <option value="<?= $personId ?>" <?= ((int) ($loanForm['person_id'] ?? 0) === $personId) ? 'selected' : '' ?>>
                            <?= htmlspecialchars((string) (($person['full_name'] ?? '') !== '' ? $person['full_name'] : ($person['social_name'] ?? '')), ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label">Data emprestimo</label>
                <input type="date" class="form-control" name="loan_date" value="<?= htmlspecialchars((string) ($loanForm['loan_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label">Previsao devolucao</label>
                <input type="date" class="form-control" name="due_date" value="<?= htmlspecialchars((string) ($loanForm['due_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label">Observacoes</label>
                <input class="form-control" name="notes" value="<?= htmlspecialchars((string) ($loanForm['notes'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-12 d-grid d-md-block">
                <button type="submit" class="btn btn-teal text-white">Registrar emprestimo</button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form method="get" action="/equipment-loans" class="row g-2">
            <div class="col-12 col-md-5">
                <input type="text" class="form-control" name="equipment_code" placeholder="Codigo do equipamento" value="<?= htmlspecialchars((string) ($filters['equipment_code'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-8 col-md-5">
                <select class="form-select" name="status">
                    <option value="">Status (todos)</option>
                    <option value="aberto" <?= ((string) ($filters['status'] ?? '') === 'aberto') ? 'selected' : '' ?>>Aberto</option>
                    <option value="atrasado" <?= ((string) ($filters['status'] ?? '') === 'atrasado') ? 'selected' : '' ?>>Atrasado</option>
                    <option value="devolvido" <?= ((string) ($filters['status'] ?? '') === 'devolvido') ? 'selected' : '' ?>>Devolvido</option>
                </select>
            </div>
            <div class="col-4 col-md-2 d-grid">
                <button type="submit" class="btn btn-outline-secondary">Buscar</button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Equipamento</th>
                    <th>Destino</th>
                    <th>Emprestimo</th>
                    <th>Previsao</th>
                    <th>Devolucao</th>
                    <th>Status</th>
                    <th>Acoes</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($loans)) : ?>
                <tr>
                    <td colspan="7" class="text-secondary p-4">Nenhum emprestimo encontrado.</td>
                </tr>
            <?php else : ?>
                <?php foreach ($loans as $loan) : ?>
                    <?php
                    $loanId = (int) ($loan['id'] ?? 0);
                    $isReturned = !empty($loan['return_date']);
                    $isOverdue = !$isReturned && !empty($loan['due_date']) && (string) $loan['due_date'] < date('Y-m-d');
                    $dest = (string) (($loan['family_name'] ?? '') !== '' ? $loan['family_name'] : (($loan['person_full_name'] ?? '') !== '' ? $loan['person_full_name'] : ($loan['person_social_name'] ?? '-')));
                    ?>
                    <tr class="<?= $isOverdue ? 'table-danger' : '' ?>">
                        <td>
                            <div class="fw-semibold"><?= htmlspecialchars((string) ($loan['equipment_code'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                            <div class="small text-secondary"><?= htmlspecialchars(ucwords(str_replace('_', ' ', (string) ($loan['equipment_type'] ?? ''))), ENT_QUOTES, 'UTF-8') ?></div>
                        </td>
                        <td><?= htmlspecialchars($dest, ENT_QUOTES, 'UTF-8') ?></td>
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
                        <td>
                            <?php if (!$isReturned) : ?>
                                <form method="post" action="/equipment-loans/return?id=<?= $loanId ?>" class="row g-1">
                                    <div class="col-12">
                                        <input type="date" class="form-control form-control-sm" name="return_date" value="<?= htmlspecialchars(date('Y-m-d'), ENT_QUOTES, 'UTF-8') ?>" required>
                                    </div>
                                    <div class="col-12">
                                        <select class="form-select form-select-sm" name="return_condition">
                                            <?php foreach ($returnConditions as $condition) : ?>
                                                <option value="<?= htmlspecialchars((string) $condition, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(ucwords((string) $condition), ENT_QUOTES, 'UTF-8') ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <input class="form-control form-control-sm" name="return_notes" placeholder="Obs devolucao">
                                    </div>
                                    <div class="col-12 d-grid">
                                        <button type="submit" class="btn btn-sm btn-outline-success">Registrar devolucao</button>
                                    </div>
                                </form>
                            <?php else : ?>
                                <span class="text-secondary small">Concluido</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

