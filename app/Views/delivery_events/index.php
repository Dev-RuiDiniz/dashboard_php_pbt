<?php
declare(strict_types=1);

$filters = is_array($filters ?? null) ? $filters : [];
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
                <h2 class="h5 mb-1">Eventos de entrega (cestas)</h2>
                <p class="text-secondary mb-0">Configuracao do evento, bloqueio mensal, limite de cestas e status.</p>
            </div>
            <a class="btn btn-teal text-white" href="/delivery-events/create">Novo evento</a>
        </div>

        <form method="get" action="/delivery-events" class="row g-2">
            <div class="col-12 col-md-5">
                <input type="text" class="form-control" name="q" placeholder="Nome do evento" value="<?= htmlspecialchars((string) ($filters['q'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-6 col-md-3">
                <select class="form-select" name="status">
                    <option value="">Status (todos)</option>
                    <?php foreach ($statuses as $status) : ?>
                        <option value="<?= htmlspecialchars((string) $status, ENT_QUOTES, 'UTF-8') ?>" <?= (($filters['status'] ?? '') === $status) ? 'selected' : '' ?>><?= htmlspecialchars((string) $status, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <input type="month" class="form-control" name="month" value="<?= htmlspecialchars((string) ($filters['month'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-12 col-md-2 d-grid">
                <button type="submit" class="btn btn-outline-secondary">Filtrar</button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Evento</th>
                    <th>Data</th>
                    <th>Regras</th>
                    <th>Status</th>
                    <th>Criado por</th>
                    <th>Acoes</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($events)) : ?>
                <tr><td colspan="6" class="text-secondary p-4">Nenhum evento encontrado.</td></tr>
            <?php else : ?>
                <?php foreach ($events as $event) : ?>
                    <?php $id = (int) ($event['id'] ?? 0); ?>
                    <tr>
                        <td>
                            <div class="fw-semibold"><?= htmlspecialchars((string) ($event['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                            <div class="small text-secondary">ID #<?= $id ?></div>
                        </td>
                        <td><?= htmlspecialchars((string) ($event['event_date'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="small">
                            <div>
                                Bloqueio mensal:
                                <?= ((int) ($event['block_multiple_same_month'] ?? 0) === 1)
                                    ? '<span class="badge text-bg-warning">Ativo</span>'
                                    : '<span class="badge text-bg-light border">Desligado</span>' ?>
                            </div>
                            <div class="mt-1">
                                Limite:
                                <?= ((string) ($event['max_baskets'] ?? '') !== '' && $event['max_baskets'] !== null)
                                    ? (int) $event['max_baskets'] . ' cesta(s)'
                                    : 'Sem limite' ?>
                            </div>
                        </td>
                        <td><span class="badge text-bg-light border"><?= htmlspecialchars((string) ($event['status'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span></td>
                        <td><?= htmlspecialchars((string) (($event['created_by_name'] ?? '') ?: '-'), ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <div class="d-flex flex-wrap gap-2">
                                <a class="btn btn-sm btn-outline-primary" href="/delivery-events/show?id=<?= $id ?>">Lista operacional</a>
                                <a class="btn btn-sm btn-outline-secondary" href="/delivery-events/edit?id=<?= $id ?>">Editar</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
