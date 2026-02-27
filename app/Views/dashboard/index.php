<?php
declare(strict_types=1);

$userName = is_array($user ?? null) ? (string) ($user['name'] ?? 'Usuario') : 'Usuario';
$userRole = is_array($user ?? null) ? (string) ($user['role'] ?? '-') : '-';
?>
<?php if (!empty($success)) : ?>
    <div class="alert alert-success shadow-sm border-0"><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<div class="row g-3 mb-3">
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card metric-card h-100">
            <div class="card-body">
                <div class="text-secondary small text-uppercase">Familias</div>
                <div class="metric-value">--</div>
                <div class="small text-secondary">Placeholder Sprint 4</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card metric-card h-100">
            <div class="card-body">
                <div class="text-secondary small text-uppercase">Pessoas acompanhadas</div>
                <div class="metric-value">--</div>
                <div class="small text-secondary">Placeholder Sprint 4</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card metric-card h-100">
            <div class="card-body">
                <div class="text-secondary small text-uppercase">Entregas do mes</div>
                <div class="metric-value">--</div>
                <div class="small text-secondary">Placeholder Sprint 4</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card metric-card h-100">
            <div class="card-body">
                <div class="text-secondary small text-uppercase">Equipamentos ativos</div>
                <div class="metric-value">--</div>
                <div class="small text-secondary">Placeholder Sprint 4</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-12 col-xl-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between gap-2 align-items-start mb-3">
                    <div>
                        <h2 class="h5 mb-1">Painel inicial</h2>
                        <p class="text-secondary mb-0">Dashboard base com layout responsivo em Bootstrap.</p>
                    </div>
                    <span class="badge text-bg-light border">Sprint 4</span>
                </div>

                <div class="placeholder-panel p-3">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <div class="bg-white rounded-3 border p-3 h-100">
                                <div class="small text-secondary text-uppercase mb-1">Usuario logado</div>
                                <div class="fw-semibold"><?= htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') ?></div>
                                <div class="small text-secondary">Perfil: <?= htmlspecialchars($userRole, ENT_QUOTES, 'UTF-8') ?></div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="bg-white rounded-3 border p-3 h-100">
                                <div class="small text-secondary text-uppercase mb-1">Status</div>
                                <div class="fw-semibold">Sessao autenticada</div>
                                <div class="small text-secondary">Rotas protegidas ativas desde Sprint 2/3</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-3 d-flex flex-wrap gap-2">
                    <a class="btn btn-outline-secondary" href="/families">Familias</a>
                    <a class="btn btn-outline-secondary" href="/children">Criancas</a>
                    <a class="btn btn-outline-secondary" href="/people">Pessoas</a>
                    <a class="btn btn-outline-secondary" href="/delivery-events">Eventos de entrega</a>
                    <a class="btn btn-outline-secondary" href="/equipment">Equipamentos</a>
                    <a class="btn btn-outline-secondary" href="/visits">Visitas</a>
                    <?php if ($userRole === 'admin') : ?>
                        <a class="btn btn-teal text-white" href="/users">Gerenciar usuarios</a>
                    <?php endif; ?>
                    <a class="btn btn-outline-secondary" href="/dashboard">Atualizar painel</a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h2 class="h6 text-uppercase text-secondary mb-3">Navegacao de modulos</h2>
                <div class="list-group list-group-flush">
                    <a class="list-group-item list-group-item-action px-0" href="/dashboard">Dashboard</a>
                    <a class="list-group-item list-group-item-action px-0" href="/families">Familias</a>
                    <a class="list-group-item list-group-item-action px-0" href="/children">Criancas</a>
                    <a class="list-group-item list-group-item-action px-0" href="/people">Pessoas acompanhadas</a>
                    <?php if ($userRole === 'admin') : ?>
                        <a class="list-group-item list-group-item-action px-0" href="/users">Usuarios (admin)</a>
                    <?php endif; ?>
                    <div class="list-group-item px-0 text-secondary">Fichas sociais (Sprint futura)</div>
                    <a class="list-group-item list-group-item-action px-0" href="/delivery-events">Eventos de entrega</a>
                    <a class="list-group-item list-group-item-action px-0" href="/equipment">Equipamentos</a>
                    <a class="list-group-item list-group-item-action px-0" href="/visits">Visitas</a>
                    <div class="list-group-item px-0 text-secondary">Relatorios (Sprint futura)</div>
                </div>
            </div>
        </div>
    </div>
</div>
