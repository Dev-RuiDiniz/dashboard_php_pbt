<?php
declare(strict_types=1);

$e = static fn (mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

$userName = is_array($user ?? null) ? (string) ($user['name'] ?? 'Usuario') : 'Usuario';
$userRole = is_array($user ?? null) ? (string) ($user['role'] ?? '-') : '-';
$summary = is_array($summary ?? null) ? $summary : [];
$familiesSummary = is_array($summary['families'] ?? null) ? $summary['families'] : [];
$peopleSummary = is_array($summary['people'] ?? null) ? $summary['people'] : [];
$deliveriesSummary = is_array($summary['deliveries'] ?? null) ? $summary['deliveries'] : [];
$pendingDocs = is_array($pendingDocs ?? null) ? $pendingDocs : [];
$pendingVisits = is_array($pendingVisits ?? null) ? $pendingVisits : [];
$staleFamilies = is_array($staleFamilies ?? null) ? $staleFamilies : [];
$overdueLoans = is_array($overdueLoans ?? null) ? $overdueLoans : [];
$priorityMap = is_array($neighborhoodPriorityMap ?? null)
    ? $neighborhoodPriorityMap
    : (is_array($neighborhoodHeatmap ?? null) ? $neighborhoodHeatmap : []);
?>

<?php if (!empty($success)) : ?>
    <div class="alert alert-success shadow-sm border-0"><?= $e($success) ?></div>
<?php endif; ?>

<div class="dashboard-beige">
    <div class="row g-3 mb-3">
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card metric-card h-100"><div class="card-body">
                <div class="text-secondary small text-uppercase">Familias</div>
                <div class="metric-value"><?= (int) ($familiesSummary['total_families'] ?? 0) ?></div>
                <div class="small text-secondary">Ativas: <?= (int) ($familiesSummary['active_families'] ?? 0) ?></div>
            </div></div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card metric-card h-100"><div class="card-body">
                <div class="text-secondary small text-uppercase">Pessoas acompanhadas</div>
                <div class="metric-value"><?= (int) ($peopleSummary['total_people'] ?? 0) ?></div>
                <div class="small text-secondary">
                    <?= $userRole === 'admin' ? 'Rua: ' . (int) ($peopleSummary['homeless_people'] ?? 0) : 'Detalhes restritos ao admin' ?>
                </div>
            </div></div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card metric-card h-100"><div class="card-body">
                <div class="text-secondary small text-uppercase">Entregas do mes</div>
                <div class="metric-value"><?= (int) ($deliveriesSummary['withdrawn_baskets'] ?? 0) ?></div>
                <div class="small text-secondary">Registros: <?= (int) ($deliveriesSummary['total_delivery_records'] ?? 0) ?></div>
            </div></div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="small text-secondary text-uppercase mb-1">Usuario</div>
                <div class="fw-semibold"><?= $e($userName) ?></div>
                <div class="small text-secondary mb-3">Perfil: <?= $e($userRole) ?></div>
                <a class="btn btn-outline-secondary btn-sm" href="/dashboard">Atualizar painel</a>
            </div></div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                <div>
                    <h2 class="h6 text-uppercase text-secondary mb-1">Mapa de prioridade social por bairro (Taubate)</h2>
                    <div class="small text-secondary">
                        Score: familias sem retirada (3), visitas pendentes (2), documentacao pendente (2),
                        cadastros sem atualizacao (1), pessoas em situacao de rua (3) e criancas vinculadas (1).
                    </div>
                </div>
                <div class="small text-secondary">
                    Quanto maior o score, maior a prioridade sugerida.
                </div>
            </div>

            <?php if (empty($priorityMap)) : ?>
                <div class="small text-secondary">Sem dados suficientes para calcular o mapa de prioridade social.</div>
            <?php else : ?>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Bairro</th>
                                <th class="text-end">Familias</th>
                                <th class="text-end">Sem retirada</th>
                                <th class="text-end">% sem atendimento</th>
                                <th class="text-end">Visitas</th>
                                <th class="text-end">Docs</th>
                                <th class="text-end">Sem update</th>
                                <th class="text-end">Rua</th>
                                <th class="text-end">Criancas</th>
                                <th class="text-end">Score</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($priorityMap as $row) : ?>
                                <?php
                                $score = max(0, (int) ($row['priority_score'] ?? 0));
                                $opacity = min(0.9, 0.12 + (0.02 * min(30, $score)));
                                ?>
                                <tr>
                                    <td><?= $e($row['neighborhood'] ?? '-') ?></td>
                                    <td class="text-end"><?= (int) ($row['total_families'] ?? 0) ?></td>
                                    <td class="text-end"><?= (int) ($row['unserved_families'] ?? 0) ?></td>
                                    <td class="text-end"><?= number_format((float) ($row['unserved_percentage'] ?? 0), 2, ',', '.') ?>%</td>
                                    <td class="text-end"><?= (int) ($row['pending_visits'] ?? 0) ?></td>
                                    <td class="text-end"><?= (int) ($row['pending_documents'] ?? 0) ?></td>
                                    <td class="text-end"><?= (int) ($row['stale_families'] ?? 0) ?></td>
                                    <td class="text-end"><?= (int) ($row['homeless_people'] ?? 0) ?></td>
                                    <td class="text-end"><?= (int) ($row['children_count'] ?? 0) ?></td>
                                    <td class="text-end">
                                        <span class="heat-badge" style="background-color: rgba(186, 138, 69, <?= number_format($opacity, 3, '.', '') ?>);">
                                            <?= $score ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-12 col-xl-6">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <h2 class="h6 text-uppercase text-secondary mb-3">Documentacao pendente</h2>
                <?php if (empty($pendingDocs)) : ?>
                    <div class="small text-secondary">Sem pendencias de documentacao.</div>
                <?php else : ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($pendingDocs as $doc) : ?>
                            <a class="list-group-item list-group-item-action px-0" href="/families/show?id=<?= (int) ($doc['id'] ?? 0) ?>">
                                <div class="fw-semibold"><?= $e($doc['responsible_name'] ?? '') ?></div>
                                <div class="small text-secondary"><?= $e($doc['documentation_status'] ?? '') ?></div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div></div>
        </div>
        <div class="col-12 col-xl-6">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <h2 class="h6 text-uppercase text-secondary mb-3">Visitas pendentes</h2>
                <?php if (empty($pendingVisits)) : ?>
                    <div class="small text-secondary">Sem visitas pendentes.</div>
                <?php else : ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($pendingVisits as $visit) : ?>
                            <a class="list-group-item list-group-item-action px-0" href="/visits">
                                <div class="fw-semibold">
                                    <?= $e(($visit['family_name'] ?? '') ?: (($visit['person_full_name'] ?? '') ?: ($visit['person_social_name'] ?? ''))) ?>
                                </div>
                                <div class="small text-secondary">
                                    <?= $e($visit['status'] ?? '') ?>
                                    <?php if (!empty($visit['scheduled_date'])) : ?>
                                        · <?= $e($visit['scheduled_date']) ?>
                                    <?php endif; ?>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div></div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-12 col-xl-6">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <h2 class="h6 text-uppercase text-secondary mb-3">Familias sem atualizacao ha mais de <?= (int) ($staleDays ?? 30) ?> dias</h2>
                <?php if (empty($staleFamilies)) : ?>
                    <div class="small text-secondary">Sem alertas de familias sem atualizacao.</div>
                <?php else : ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($staleFamilies as $family) : ?>
                            <a class="list-group-item list-group-item-action px-0" href="/families/show?id=<?= (int) ($family['id'] ?? 0) ?>">
                                <div class="fw-semibold"><?= $e($family['responsible_name'] ?? '') ?></div>
                                <div class="small text-secondary">
                                    <?= $e(trim((string) (($family['neighborhood'] ?? '') . ' ' . ($family['city'] ?? '')))) ?>
                                    · <?= $e($family['updated_at'] ?? '') ?>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div></div>
        </div>
        <div class="col-12 col-xl-6">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <h2 class="h6 text-uppercase text-secondary mb-3">Devolucoes atrasadas</h2>
                <?php if (empty($overdueLoans)) : ?>
                    <div class="small text-secondary">Sem devolucoes atrasadas no momento.</div>
                <?php else : ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($overdueLoans as $loan) : ?>
                            <a class="list-group-item list-group-item-action px-0" href="/equipment-loans">
                                <div class="fw-semibold">
                                    <?= $e($loan['equipment_code'] ?? '') ?> · <?= $e($loan['equipment_type'] ?? '') ?>
                                </div>
                                <div class="small text-secondary">
                                    <?= $e(($loan['family_name'] ?? '') ?: (($loan['person_full_name'] ?? '') ?: ($loan['person_social_name'] ?? '-'))) ?>
                                    · venc.: <?= $e($loan['due_date'] ?? '') ?>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div></div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <h2 class="h6 text-uppercase text-secondary mb-3">Acoes rapidas</h2>
            <div class="d-flex flex-wrap gap-2">
                <a class="btn btn-outline-secondary" href="/families/create">Nova familia</a>
                <?php if ($userRole === 'admin') : ?>
                    <a class="btn btn-outline-secondary" href="/people/create">Novo atendimento</a>
                    <a class="btn btn-outline-secondary" href="/social-records">Fichas sociais</a>
                <?php endif; ?>
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
