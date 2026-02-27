<?php
declare(strict_types=1);

$filters = is_array($filters ?? null) ? $filters : [];
$types = is_array($types ?? null) ? $types : [];
$statuses = is_array($statuses ?? null) ? $statuses : [];
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
                <h2 class="h5 mb-1">Estoque de equipamentos</h2>
                <p class="text-secondary mb-0">Filtro por tipo, status e codigo.</p>
            </div>
            <a class="btn btn-teal text-white" href="/equipment/create">Novo equipamento</a>
        </div>

        <form method="get" action="/equipment" class="row g-2">
            <div class="col-12 col-md-4">
                <input type="text" class="form-control" name="code" placeholder="Codigo" value="<?= htmlspecialchars((string) ($filters['code'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-6 col-md-3">
                <select class="form-select" name="type">
                    <option value="">Tipo (todos)</option>
                    <?php foreach ($types as $type) : ?>
                        <option value="<?= htmlspecialchars((string) $type, ENT_QUOTES, 'UTF-8') ?>" <?= ((string) ($filters['type'] ?? '') === (string) $type) ? 'selected' : '' ?>>
                            <?= htmlspecialchars(ucwords(str_replace('_', ' ', (string) $type)), ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <select class="form-select" name="status">
                    <option value="">Status (todos)</option>
                    <?php foreach ($statuses as $status) : ?>
                        <option value="<?= htmlspecialchars((string) $status, ENT_QUOTES, 'UTF-8') ?>" <?= ((string) ($filters['status'] ?? '') === (string) $status) ? 'selected' : '' ?>>
                            <?= htmlspecialchars(ucwords(str_replace('_', ' ', (string) $status)), ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-2 d-grid">
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
                    <th>Codigo</th>
                    <th>Tipo</th>
                    <th>Estado</th>
                    <th>Status</th>
                    <th>Observacoes</th>
                    <th>Acoes</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($equipments)) : ?>
                <tr>
                    <td colspan="6" class="text-secondary p-4">Nenhum equipamento encontrado.</td>
                </tr>
            <?php else : ?>
                <?php foreach ($equipments as $equipment) : ?>
                    <?php $equipmentId = (int) ($equipment['id'] ?? 0); ?>
                    <tr>
                        <td class="fw-semibold"><?= htmlspecialchars((string) ($equipment['code'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars(ucwords(str_replace('_', ' ', (string) ($equipment['type'] ?? ''))), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars(ucwords(str_replace('_', ' ', (string) ($equipment['condition_state'] ?? ''))), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars(ucwords(str_replace('_', ' ', (string) ($equipment['status'] ?? ''))), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) ($equipment['notes'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <div class="d-flex flex-wrap gap-2">
                                <a class="btn btn-sm btn-outline-secondary" href="/equipment/edit?id=<?= $equipmentId ?>">Editar</a>
                                <form method="post" action="/equipment/delete?id=<?= $equipmentId ?>" class="m-0" onsubmit="return confirm('Remover equipamento?');">
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

