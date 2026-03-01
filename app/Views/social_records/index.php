<?php
declare(strict_types=1);

$records = is_array($records ?? null) ? $records : [];
$summary = is_array($summary ?? null) ? $summary : [];
$filters = is_array($filters ?? null) ? $filters : [];
?>

<?php if (!empty($success)) : ?>
    <div class="alert alert-success shadow-sm border-0"><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>
<?php if (!empty($error)) : ?>
    <div class="alert alert-danger shadow-sm border-0"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<div class="d-flex flex-wrap gap-2 mb-3">
    <a class="btn btn-outline-secondary" href="/people">Ir para pessoas acompanhadas</a>
    <a class="btn btn-outline-primary" href="/people/create">Nova pessoa acompanhada</a>
</div>

<form method="get" action="/social-records" class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-12 col-lg-4">
                <label class="form-label">Busca</label>
                <input class="form-control" name="q" placeholder="Pessoa, consentimento ou necessidade" value="<?= htmlspecialchars((string) ($filters['q'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-6 col-lg-2">
                <label class="form-label">Data inicial</label>
                <input type="date" class="form-control" name="date_from" value="<?= htmlspecialchars((string) ($filters['date_from'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-6 col-lg-2">
                <label class="form-label">Data final</label>
                <input type="date" class="form-control" name="date_to" value="<?= htmlspecialchars((string) ($filters['date_to'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-6 col-lg-2">
                <label class="form-label">Vinculo familiar</label>
                <select class="form-select" name="has_family">
                    <option value="" <?= ((string) ($filters['has_family'] ?? '') === '') ? 'selected' : '' ?>>Todos</option>
                    <option value="1" <?= ((string) ($filters['has_family'] ?? '') === '1') ? 'selected' : '' ?>>Com familia</option>
                    <option value="0" <?= ((string) ($filters['has_family'] ?? '') === '0') ? 'selected' : '' ?>>Sem familia</option>
                </select>
            </div>
            <div class="col-6 col-lg-2">
                <label class="form-label">Espiritual</label>
                <select class="form-select" name="spiritual">
                    <option value="" <?= ((string) ($filters['spiritual'] ?? '') === '') ? 'selected' : '' ?>>Todos</option>
                    <option value="prayer" <?= ((string) ($filters['spiritual'] ?? '') === 'prayer') ? 'selected' : '' ?>>Deseja oracao</option>
                    <option value="visit" <?= ((string) ($filters['spiritual'] ?? '') === 'visit') ? 'selected' : '' ?>>Aceita visita</option>
                </select>
            </div>
        </div>
        <div class="d-flex gap-2 mt-3">
            <button type="submit" class="btn btn-teal text-white">Filtrar fichas</button>
            <a class="btn btn-outline-secondary" href="/social-records">Limpar</a>
        </div>
    </div>
</form>

<div class="row g-3 mb-3">
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card metric-card h-100"><div class="card-body">
            <div class="small text-secondary text-uppercase">Total fichas</div>
            <div class="metric-value"><?= (int) ($summary['total_records'] ?? 0) ?></div>
        </div></div>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card metric-card h-100"><div class="card-body">
            <div class="small text-secondary text-uppercase">Com familia</div>
            <div class="metric-value"><?= (int) ($summary['linked_family'] ?? 0) ?></div>
        </div></div>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card metric-card h-100"><div class="card-body">
            <div class="small text-secondary text-uppercase">Deseja oracao</div>
            <div class="metric-value"><?= (int) ($summary['wants_prayer'] ?? 0) ?></div>
        </div></div>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card metric-card h-100"><div class="card-body">
            <div class="small text-secondary text-uppercase">Aceita visita</div>
            <div class="metric-value"><?= (int) ($summary['accepts_visit'] ?? 0) ?></div>
        </div></div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <h2 class="h5 mb-3">Lista de fichas sociais</h2>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Pessoa</th>
                        <th>Familia</th>
                        <th>Necessidades</th>
                        <th>Espiritual</th>
                        <th>Consentimento</th>
                        <th>Responsavel</th>
                        <th>Acoes</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($records)) : ?>
                    <tr><td colspan="8" class="text-secondary">Nenhuma ficha social encontrada para os filtros informados.</td></tr>
                <?php else : ?>
                    <?php foreach ($records as $record) : ?>
                        <?php
                        $personName = (string) (($record['full_name'] ?? '') ?: ($record['social_name'] ?? 'Sem identificacao'));
                        $personId = (int) ($record['person_id'] ?? 0);
                        ?>
                        <tr>
                            <td><?= htmlspecialchars((string) ($record['created_at'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <div class="fw-semibold"><?= htmlspecialchars($personName, ENT_QUOTES, 'UTF-8') ?></div>
                                <div class="small text-secondary">Pessoa #<?= $personId ?></div>
                            </td>
                            <td><?= htmlspecialchars((string) (($record['family_name'] ?? '') ?: 'Sem vinculo'), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) (($record['immediate_needs'] ?? '') ?: '-'), ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <?php if ((int) ($record['spiritual_wants_prayer'] ?? 0) === 1) : ?>
                                    <span class="badge text-bg-warning">oracao</span>
                                <?php endif; ?>
                                <?php if ((int) ($record['spiritual_accepts_visit'] ?? 0) === 1) : ?>
                                    <span class="badge text-bg-info">visita</span>
                                <?php endif; ?>
                                <?php if ((int) ($record['spiritual_wants_prayer'] ?? 0) !== 1 && (int) ($record['spiritual_accepts_visit'] ?? 0) !== 1) : ?>
                                    <span class="text-secondary">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="fw-semibold"><?= htmlspecialchars((string) ($record['consent_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                <div class="small text-secondary"><?= htmlspecialchars((string) ($record['consent_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                            </td>
                            <td><?= htmlspecialchars((string) (($record['created_by_name'] ?? '') ?: '-'), ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <a class="btn btn-sm btn-outline-secondary" href="/people/show?id=<?= $personId ?>">Ver pessoa</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
