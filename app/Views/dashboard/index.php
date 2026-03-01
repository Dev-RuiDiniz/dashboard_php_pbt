<?php
declare(strict_types=1);

$userName = is_array($user ?? null) ? (string) ($user['name'] ?? 'Usuario') : 'Usuario';
$userRole = is_array($user ?? null) ? (string) ($user['role'] ?? '-') : '-';
$summary = is_array($summary ?? null) ? $summary : [];
$familiesSummary = is_array($summary['families'] ?? null) ? $summary['families'] : [];
$peopleSummary = is_array($summary['people'] ?? null) ? $summary['people'] : [];
$childrenSummary = is_array($summary['children'] ?? null) ? $summary['children'] : [];
$deliveriesSummary = is_array($summary['deliveries'] ?? null) ? $summary['deliveries'] : [];
$referralsSummary = is_array($summary['referrals'] ?? null) ? $summary['referrals'] : [];
$equipmentSummary = is_array($summary['equipment'] ?? null) ? $summary['equipment'] : [];
$pendingDocs = is_array($pendingDocs ?? null) ? $pendingDocs : [];
$pendingVisits = is_array($pendingVisits ?? null) ? $pendingVisits : [];
$staleFamilies = is_array($staleFamilies ?? null) ? $staleFamilies : [];
$overdueLoans = is_array($overdueLoans ?? null) ? $overdueLoans : [];
?>
<?php if (!empty($success)) : ?>
    <div class="alert alert-success shadow-sm border-0"><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<div class="dashboard-beige">
<div class="row g-3 mb-3">
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card metric-card h-100">
            <div class="card-body">
                <div class="text-secondary small text-uppercase">Familias</div>
                <div class="metric-value"><?= (int) ($familiesSummary['total_families'] ?? 0) ?></div>
                <div class="small text-secondary">Ativas: <?= (int) ($familiesSummary['active_families'] ?? 0) ?></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card metric-card h-100">
            <div class="card-body">
                <div class="text-secondary small text-uppercase">Pessoas acompanhadas</div>
                <div class="metric-value"><?= (int) ($peopleSummary['total_people'] ?? 0) ?></div>
                <div class="small text-secondary">Rua: <?= (int) ($peopleSummary['homeless_people'] ?? 0) ?></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card metric-card h-100">
            <div class="card-body">
                <div class="text-secondary small text-uppercase">Criancas</div>
                <div class="metric-value"><?= (int) ($childrenSummary['total_children'] ?? 0) ?></div>
                <div class="small text-secondary">Total vinculado a familias</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card metric-card h-100">
            <div class="card-body">
                <div class="text-secondary small text-uppercase">Entregas do mes</div>
                <div class="metric-value"><?= (int) ($deliveriesSummary['withdrawn_baskets'] ?? 0) ?></div>
                <div class="small text-secondary">Registros: <?= (int) ($deliveriesSummary['total_delivery_records'] ?? 0) ?></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="small text-secondary text-uppercase mb-1">Encaminhamentos (mes)</div>
                <div class="h3 mb-1"><?= (int) ($referralsSummary['total_referrals'] ?? 0) ?></div>
                <div class="small text-secondary">Periodo: <?= htmlspecialchars((string) ($periodStart ?? ''), ENT_QUOTES, 'UTF-8') ?> a <?= htmlspecialchars((string) ($periodEnd ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="small text-secondary text-uppercase mb-1">Equipamentos</div>
                <div class="small mb-1">Total: <strong><?= (int) ($equipmentSummary['total_equipment'] ?? 0) ?></strong></div>
                <div class="small mb-1">Disponiveis: <strong><?= (int) ($equipmentSummary['available_equipment'] ?? 0) ?></strong></div>
                <div class="small mb-1">Emprestados: <strong><?= (int) ($equipmentSummary['loaned_equipment'] ?? 0) ?></strong></div>
                <div class="small mb-1">Manutencao: <strong><?= (int) ($equipmentSummary['maintenance_equipment'] ?? 0) ?></strong></div>
                <div class="small">Inativos: <strong><?= (int) ($equipmentSummary['inactive_equipment'] ?? 0) ?></strong></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="small text-secondary text-uppercase mb-1">Alertas</div>
                <div class="small mb-1">Docs pendentes: <strong><?= (int) ($pendingDocsCount ?? 0) ?></strong></div>
                <div class="small mb-1">Visitas pendentes: <strong><?= (int) ($pendingVisitsCount ?? 0) ?></strong></div>
                <div class="small mb-1">Familias sem update: <strong><?= (int) ($staleFamiliesCount ?? 0) ?></strong></div>
                <div class="small">Devolucoes atrasadas: <strong><?= (int) ($overdueLoansCount ?? 0) ?></strong></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="small text-secondary text-uppercase mb-1">Usuario</div>
                <div class="fw-semibold"><?= htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') ?></div>
                <div class="small text-secondary mb-3">Perfil: <?= htmlspecialchars($userRole, ENT_QUOTES, 'UTF-8') ?></div>
                <a class="btn btn-outline-secondary btn-sm" href="/dashboard">Atualizar painel</a>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-12 col-xl-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h2 class="h6 text-uppercase text-secondary mb-3">Documentacao pendente</h2>
                <?php if (empty($pendingDocs)) : ?>
                    <div class="small text-secondary">Sem pendencias de documentacao.</div>
                <?php else : ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($pendingDocs as $doc) : ?>
                            <a class="list-group-item list-group-item-action px-0" href="/families/show?id=<?= (int) ($doc['id'] ?? 0) ?>">
                                <div class="fw-semibold"><?= htmlspecialchars((string) ($doc['responsible_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                <div class="small text-secondary"><?= htmlspecialchars((string) ($doc['documentation_status'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h2 class="h6 text-uppercase text-secondary mb-3">Visitas pendentes</h2>
                <?php if (empty($pendingVisits)) : ?>
                    <div class="small text-secondary">Sem visitas pendentes.</div>
                <?php else : ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($pendingVisits as $visit) : ?>
                            <a class="list-group-item list-group-item-action px-0" href="/visits">
                                <div class="fw-semibold">
                                    <?= htmlspecialchars((string) (($visit['family_name'] ?? '') ?: (($visit['person_full_name'] ?? '') ?: ($visit['person_social_name'] ?? ''))), ENT_QUOTES, 'UTF-8') ?>
                                </div>
                                <div class="small text-secondary">
                                    <?= htmlspecialchars((string) ($visit['status'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                    <?php if (!empty($visit['scheduled_date'])) : ?>
                                        · <?= htmlspecialchars((string) $visit['scheduled_date'], ENT_QUOTES, 'UTF-8') ?>
                                    <?php endif; ?>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-12 col-xl-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h2 class="h6 text-uppercase text-secondary mb-3">Familias sem atualizacao ha mais de <?= (int) ($staleDays ?? 30) ?> dias</h2>
                <?php if (empty($staleFamilies)) : ?>
                    <div class="small text-secondary">Sem alertas de familias sem atualizacao.</div>
                <?php else : ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($staleFamilies as $family) : ?>
                            <a class="list-group-item list-group-item-action px-0" href="/families/show?id=<?= (int) ($family['id'] ?? 0) ?>">
                                <div class="fw-semibold"><?= htmlspecialchars((string) ($family['responsible_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                <div class="small text-secondary">
                                    <?= htmlspecialchars(trim((string) (($family['neighborhood'] ?? '') . ' ' . ($family['city'] ?? ''))), ENT_QUOTES, 'UTF-8') ?>
                                    · <?= htmlspecialchars((string) ($family['updated_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h2 class="h6 text-uppercase text-secondary mb-3">Devolucoes atrasadas</h2>
                <?php if (empty($overdueLoans)) : ?>
                    <div class="small text-secondary">Sem devolucoes atrasadas no momento.</div>
                <?php else : ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($overdueLoans as $loan) : ?>
                            <a class="list-group-item list-group-item-action px-0" href="/equipment-loans">
                                <div class="fw-semibold">
                                    <?= htmlspecialchars((string) ($loan['equipment_code'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                    · <?= htmlspecialchars((string) ($loan['equipment_type'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                </div>
                                <div class="small text-secondary">
                                    <?= htmlspecialchars((string) (($loan['family_name'] ?? '') ?: (($loan['person_full_name'] ?? '') ?: ($loan['person_social_name'] ?? '-'))), ENT_QUOTES, 'UTF-8') ?>
                                    · venc.: <?= htmlspecialchars((string) ($loan['due_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <h2 class="h6 text-uppercase text-secondary mb-3">Acoes rapidas</h2>
        <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-outline-secondary" href="/families/create">Nova familia</a>
            <a class="btn btn-outline-secondary" href="/people/create">Novo atendimento</a>
            <a class="btn btn-outline-secondary" href="/social-records">Fichas sociais</a>
            <a class="btn btn-outline-secondary" href="/delivery-events/create">Criar evento de entrega</a>
            <a class="btn btn-outline-secondary" href="/equipment-loans">Registrar emprestimo</a>
            <a class="btn btn-outline-secondary" href="/visits/create">Solicitar visita</a>
            <a class="btn btn-outline-secondary" href="/reports">Relatorios</a>
            <?php if ($userRole === 'admin') : ?>
                <a class="btn btn-teal text-white" href="/users">Gerenciar usuarios</a>
            <?php endif; ?>
        </div>
    </div>
</div>
</div>
