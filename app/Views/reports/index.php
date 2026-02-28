<?php
declare(strict_types=1);

$filters = is_array($filters ?? null) ? $filters : [];
$reportData = is_array($reportData ?? null) ? $reportData : [];
$families = is_array($reportData['families'] ?? null) ? $reportData['families'] : ['summary' => [], 'items' => []];
$baskets = is_array($reportData['baskets'] ?? null) ? $reportData['baskets'] : ['summary' => [], 'items' => []];
$children = is_array($reportData['children'] ?? null) ? $reportData['children'] : ['summary' => [], 'items' => []];
$referrals = is_array($reportData['referrals'] ?? null) ? $reportData['referrals'] : ['summary' => [], 'items' => []];
$equipment = is_array($reportData['equipment'] ?? null) ? $reportData['equipment'] : ['summary' => [], 'items' => []];
$pendencies = is_array($reportData['pendencies'] ?? null) ? $reportData['pendencies'] : ['summary' => [], 'items' => []];
$pendencyItems = is_array($pendencies['items'] ?? null) ? $pendencies['items'] : [];
?>
<?php if (!empty($success)) : ?>
    <div class="alert alert-success shadow-sm border-0"><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>
<?php if (!empty($error)) : ?>
    <div class="alert alert-danger shadow-sm border-0"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
            <div>
                <h2 class="h5 mb-1">Relatorios mensais</h2>
                <p class="text-secondary mb-0">Familias, cestas, criancas, encaminhamentos, equipamentos e pendencias.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a class="btn btn-outline-secondary" href="/reports/pdf?<?= htmlspecialchars(http_build_query($filters), ENT_QUOTES, 'UTF-8') ?>">Exportar PDF</a>
                <a class="btn btn-outline-secondary" href="/reports/csv?<?= htmlspecialchars(http_build_query($filters), ENT_QUOTES, 'UTF-8') ?>">Exportar CSV</a>
                <a class="btn btn-outline-secondary" href="/reports/excel?<?= htmlspecialchars(http_build_query($filters), ENT_QUOTES, 'UTF-8') ?>">Exportar Excel</a>
            </div>
        </div>

        <form method="get" action="/reports" class="row g-2">
            <div class="col-6 col-md-2">
                <label class="form-label small mb-1">Inicio</label>
                <input type="date" class="form-control" name="period_start" value="<?= htmlspecialchars((string) ($filters['period_start'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small mb-1">Fim</label>
                <input type="date" class="form-control" name="period_end" value="<?= htmlspecialchars((string) ($filters['period_end'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small mb-1">Status</label>
                <input class="form-control" name="status" placeholder="ativo, retirou, concluido..." value="<?= htmlspecialchars((string) ($filters['status'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small mb-1">Bairro</label>
                <input class="form-control" name="neighborhood" placeholder="bairro" value="<?= htmlspecialchars((string) ($filters['neighborhood'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-12 col-md-2 d-grid align-self-end">
                <button type="submit" class="btn btn-teal text-white">Filtrar</button>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-12 col-md-6 col-xl-2">
        <div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="small text-secondary">Familias</div><div class="h4 mb-0"><?= (int) (($families['summary']['total_families'] ?? 0)) ?></div></div></div>
    </div>
    <div class="col-12 col-md-6 col-xl-2">
        <div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="small text-secondary">Cestas</div><div class="h4 mb-0"><?= (int) (($baskets['summary']['total_baskets'] ?? 0)) ?></div></div></div>
    </div>
    <div class="col-12 col-md-6 col-xl-2">
        <div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="small text-secondary">Criancas</div><div class="h4 mb-0"><?= (int) (($children['summary']['total_children'] ?? 0)) ?></div></div></div>
    </div>
    <div class="col-12 col-md-6 col-xl-2">
        <div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="small text-secondary">Encaminhamentos</div><div class="h4 mb-0"><?= (int) (($referrals['summary']['total_referrals'] ?? 0)) ?></div></div></div>
    </div>
    <div class="col-12 col-md-6 col-xl-2">
        <div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="small text-secondary">Emprestimos abertos</div><div class="h4 mb-0"><?= (int) (($equipment['summary']['open_loans'] ?? 0)) ?></div></div></div>
    </div>
    <div class="col-12 col-md-6 col-xl-2">
        <div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="small text-secondary">Pendencias</div><div class="h4 mb-0"><?= (int) (($pendencies['summary']['pending_documentation'] ?? 0) + ($pendencies['summary']['pending_visits'] ?? 0)) ?></div></div></div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <h3 class="h6 mb-2">Familias (amostra)</h3>
        <div class="small text-secondary mb-2">Ativas: <?= (int) (($families['summary']['active_families'] ?? 0)) ?> | Inativas: <?= (int) (($families['summary']['inactive_families'] ?? 0)) ?></div>
        <div class="table-responsive">
            <table class="table table-sm mb-0"><thead><tr><th>Responsavel</th><th>Bairro</th><th>Status</th></tr></thead><tbody>
            <?php foreach (array_slice((array) ($families['items'] ?? []), 0, 10) as $item) : ?>
                <tr><td><?= htmlspecialchars((string) ($item['responsible_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars((string) ($item['neighborhood'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td><td><?= ((int) ($item['is_active'] ?? 0) === 1) ? 'ativo' : 'inativo' ?></td></tr>
            <?php endforeach; ?>
            </tbody></table>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <h3 class="h6 mb-2">Cestas (amostra)</h3>
        <div class="small text-secondary mb-2">Retiradas: <?= (int) (($baskets['summary']['withdrawn_baskets'] ?? 0)) ?></div>
        <div class="table-responsive">
            <table class="table table-sm mb-0"><thead><tr><th>Evento</th><th>Familia</th><th>Qtd</th><th>Status</th></tr></thead><tbody>
            <?php foreach (array_slice((array) ($baskets['items'] ?? []), 0, 10) as $item) : ?>
                <tr><td><?= htmlspecialchars((string) ($item['event_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars((string) ($item['family_name'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td><td><?= (int) ($item['quantity'] ?? 0) ?></td><td><?= htmlspecialchars((string) ($item['status'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td></tr>
            <?php endforeach; ?>
            </tbody></table>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <h3 class="h6 mb-2">Criancas e encaminhamentos (amostra)</h3>
        <div class="row g-3">
            <div class="col-12 col-lg-6">
                <table class="table table-sm mb-0"><thead><tr><th>Crianca</th><th>Familia</th></tr></thead><tbody>
                <?php foreach (array_slice((array) ($children['items'] ?? []), 0, 10) as $item) : ?>
                    <tr><td><?= htmlspecialchars((string) ($item['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars((string) ($item['family_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td></tr>
                <?php endforeach; ?>
                </tbody></table>
            </div>
            <div class="col-12 col-lg-6">
                <table class="table table-sm mb-0"><thead><tr><th>Tipo</th><th>Pessoa</th><th>Status</th></tr></thead><tbody>
                <?php foreach (array_slice((array) ($referrals['items'] ?? []), 0, 10) as $item) : ?>
                    <tr><td><?= htmlspecialchars((string) ($item['referral_type'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars((string) ($item['person_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars((string) ($item['status'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td></tr>
                <?php endforeach; ?>
                </tbody></table>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <h3 class="h6 mb-2">Equipamentos (amostra)</h3>
        <div class="small text-secondary mb-2">
            Emprestados: <?= (int) (($equipment['summary']['open_loans'] ?? 0)) ?> |
            Devolvidos: <?= (int) (($equipment['summary']['returned_loans'] ?? 0)) ?> |
            Atrasados: <?= (int) (($equipment['summary']['overdue_loans'] ?? 0)) ?> |
            Em manutencao: <?= (int) (($equipment['summary']['maintenance_equipment'] ?? 0)) ?>
        </div>
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead><tr><th>Equipamento</th><th>Destino</th><th>Situacao</th><th>Vencimento</th></tr></thead>
                <tbody>
                <?php foreach (array_slice((array) ($equipment['items'] ?? []), 0, 12) as $item) : ?>
                    <?php
                    $loanStatus = ((string) ($item['return_date'] ?? '') !== '')
                        ? 'devolvido'
                        : (((string) ($item['due_date'] ?? '') < date('Y-m-d')) ? 'atrasado' : 'emprestado');
                    $target = (string) (($item['family_name'] ?? '') ?: ($item['person_name'] ?? '-'));
                    ?>
                    <tr>
                        <td><?= htmlspecialchars((string) (($item['equipment_code'] ?? '') . ' - ' . ($item['equipment_type'] ?? '')), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($target, ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($loanStatus, ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) ($item['due_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <h3 class="h6 mb-2">Pendencias (amostra)</h3>
        <div class="small text-secondary mb-2">
            Documentacao: <?= (int) (($pendencies['summary']['pending_documentation'] ?? 0)) ?> |
            Visitas: <?= (int) (($pendencies['summary']['pending_visits'] ?? 0)) ?> |
            Devolucoes atrasadas: <?= (int) (($pendencies['summary']['overdue_returns'] ?? 0)) ?> |
            Sem atualizacao: <?= (int) (($pendencies['summary']['stale_updates'] ?? 0)) ?>
        </div>
        <div class="row g-3">
            <div class="col-12 col-lg-4">
                <div class="fw-semibold mb-2">Documentacao</div>
                <ul class="list-group list-group-flush">
                    <?php foreach (array_slice((array) ($pendencyItems['documentation'] ?? []), 0, 8) as $item) : ?>
                        <li class="list-group-item px-0 small">
                            <?= htmlspecialchars((string) ($item['responsible_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                            (<?= htmlspecialchars((string) ($item['documentation_status'] ?? ''), ENT_QUOTES, 'UTF-8') ?>)
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="col-12 col-lg-4">
                <div class="fw-semibold mb-2">Visitas</div>
                <ul class="list-group list-group-flush">
                    <?php foreach (array_slice((array) ($pendencyItems['visits'] ?? []), 0, 8) as $item) : ?>
                        <li class="list-group-item px-0 small">
                            <?= htmlspecialchars((string) (($item['family_name'] ?? '') ?: ($item['person_name'] ?? '')), ENT_QUOTES, 'UTF-8') ?>
                            (<?= htmlspecialchars((string) ($item['status'] ?? ''), ENT_QUOTES, 'UTF-8') ?>)
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="col-12 col-lg-4">
                <div class="fw-semibold mb-2">Devolucoes atrasadas</div>
                <ul class="list-group list-group-flush">
                    <?php foreach (array_slice((array) ($pendencyItems['overdue_returns'] ?? []), 0, 8) as $item) : ?>
                        <li class="list-group-item px-0 small">
                            <?= htmlspecialchars((string) (($item['equipment_code'] ?? '') . ' - ' . ($item['equipment_type'] ?? '')), ENT_QUOTES, 'UTF-8') ?>
                            (venc: <?= htmlspecialchars((string) ($item['due_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?>)
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>
