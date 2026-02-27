<?php
declare(strict_types=1);

$filters = is_array($filters ?? null) ? $filters : [];
$statuses = is_array($statuses ?? null) ? $statuses : [];
$pendingDocs = is_array($pendingDocs ?? null) ? $pendingDocs : [];
$pendingVisits = is_array($pendingVisits ?? null) ? $pendingVisits : [];
$staleUpdates = is_array($staleUpdates ?? null) ? $staleUpdates : [];
?>
<?php if (!empty($success)) : ?>
    <div class="alert alert-success shadow-sm border-0"><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>
<?php if (!empty($error)) : ?>
    <div class="alert alert-danger shadow-sm border-0"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<div class="row g-3 mb-3">
    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="small text-secondary text-uppercase">Docs pendentes</div>
                <div class="h3 mb-2"><?= (int) ($pendingDocsCount ?? 0) ?></div>
                <div class="small text-secondary">
                    <?php foreach ($pendingDocs as $idx => $doc) : ?>
                        <?php if ($idx >= 3) {
                            break;
                        } ?>
                        <?= htmlspecialchars((string) ($doc['responsible_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                        <?php if ($idx < min(2, count($pendingDocs) - 1)) : ?>, <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="small text-secondary text-uppercase">Visitas pendentes</div>
                <div class="h3 mb-2"><?= (int) ($pendingVisitsCount ?? 0) ?></div>
                <div class="small text-secondary">Status pendente/agendada.</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="small text-secondary text-uppercase">Sem atualizacao</div>
                <div class="h3 mb-2"><?= (int) ($staleUpdatesCount ?? 0) ?></div>
                <div class="small text-secondary">Itens sem update ha mais de <?= (int) ($staleDays ?? 30) ?> dias.</div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
            <div>
                <h2 class="h5 mb-1">Lista de visitas</h2>
                <p class="text-secondary mb-0">Solicitacoes, conclusoes e alertas operacionais.</p>
            </div>
            <a class="btn btn-teal text-white" href="/visits/create">Solicitar visita</a>
        </div>

        <form method="get" action="/visits" class="row g-2">
            <div class="col-12 col-md-5">
                <input type="text" class="form-control" name="q" placeholder="Familia, pessoa ou observacao" value="<?= htmlspecialchars((string) ($filters['q'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-6 col-md-3">
                <select class="form-select" name="status">
                    <option value="">Status (todos)</option>
                    <?php foreach ($statuses as $status) : ?>
                        <option value="<?= htmlspecialchars((string) $status, ENT_QUOTES, 'UTF-8') ?>" <?= ((string) ($filters['status'] ?? '') === (string) $status) ? 'selected' : '' ?>>
                            <?= htmlspecialchars(ucwords((string) $status), ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select class="form-select" name="pendency">
                    <option value="">Pendencia</option>
                    <option value="pendente" <?= ((string) ($filters['pendency'] ?? '') === 'pendente') ? 'selected' : '' ?>>Pendentes</option>
                    <option value="atrasada" <?= ((string) ($filters['pendency'] ?? '') === 'atrasada') ? 'selected' : '' ?>>Atrasadas</option>
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
                    <th>Destino</th>
                    <th>Solicitada</th>
                    <th>Agendada</th>
                    <th>Status</th>
                    <th>Observacoes</th>
                    <th>Acoes</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($visits)) : ?>
                <tr>
                    <td colspan="6" class="text-secondary p-4">Nenhuma visita encontrada.</td>
                </tr>
            <?php else : ?>
                <?php foreach ($visits as $visit) : ?>
                    <?php
                    $visitId = (int) ($visit['id'] ?? 0);
                    $status = (string) ($visit['status'] ?? 'pendente');
                    $isDone = $status === 'concluida';
                    $isLate = !$isDone && !empty($visit['scheduled_date']) && (string) $visit['scheduled_date'] < date('Y-m-d');
                    $dest = (string) (($visit['family_name'] ?? '') !== '' ? $visit['family_name'] : (($visit['person_full_name'] ?? '') !== '' ? $visit['person_full_name'] : ($visit['person_social_name'] ?? '-')));
                    ?>
                    <tr class="<?= $isLate ? 'table-danger' : '' ?>">
                        <td class="fw-semibold"><?= htmlspecialchars($dest, ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <div><?= htmlspecialchars((string) ($visit['requested_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                            <div class="small text-secondary"><?= htmlspecialchars((string) ($visit['requested_by_name'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>
                        </td>
                        <td><?= htmlspecialchars((string) ($visit['scheduled_date'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <?php if ($status === 'concluida') : ?>
                                <span class="badge text-bg-success">Concluida</span>
                            <?php elseif ($status === 'cancelada') : ?>
                                <span class="badge text-bg-secondary">Cancelada</span>
                            <?php elseif ($isLate) : ?>
                                <span class="badge text-bg-danger">Atrasada</span>
                            <?php elseif ($status === 'agendada') : ?>
                                <span class="badge text-bg-warning">Agendada</span>
                            <?php else : ?>
                                <span class="badge text-bg-primary">Pendente</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars((string) ($visit['notes'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <div class="d-flex flex-wrap gap-2">
                                <?php if (!$isDone) : ?>
                                    <form method="post" action="/visits/conclude?id=<?= $visitId ?>" class="d-flex gap-1">
                                        <input class="form-control form-control-sm" name="completion_notes" placeholder="Resumo conclusao">
                                        <button type="submit" class="btn btn-sm btn-outline-success">Concluir</button>
                                    </form>
                                <?php endif; ?>
                                <a class="btn btn-sm btn-outline-secondary" href="/visits/edit?id=<?= $visitId ?>">Editar</a>
                                <form method="post" action="/visits/delete?id=<?= $visitId ?>" class="m-0" onsubmit="return confirm('Remover visita?');">
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

<div class="row g-3 mt-1">
    <div class="col-12 col-xl-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h3 class="h6 mb-2">Pendencias de documentacao</h3>
                <?php if (empty($pendingDocs)) : ?>
                    <div class="small text-secondary">Sem pendencias no momento.</div>
                <?php else : ?>
                    <ul class="mb-0">
                        <?php foreach ($pendingDocs as $doc) : ?>
                            <li>
                                <?= htmlspecialchars((string) ($doc['responsible_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                (<?= htmlspecialchars((string) ($doc['documentation_status'] ?? ''), ENT_QUOTES, 'UTF-8') ?>)
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h3 class="h6 mb-2">Sem atualizacao ha mais de <?= (int) ($staleDays ?? 30) ?> dias</h3>
                <?php if (empty($staleUpdates)) : ?>
                    <div class="small text-secondary">Sem alertas de atualizacao no momento.</div>
                <?php else : ?>
                    <ul class="mb-0">
                        <?php foreach ($staleUpdates as $item) : ?>
                            <li>
                                <?= htmlspecialchars((string) ($item['entity_type'] ?? ''), ENT_QUOTES, 'UTF-8') ?>:
                                <?= htmlspecialchars((string) ($item['label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

