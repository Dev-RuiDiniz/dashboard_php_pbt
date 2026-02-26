<?php
declare(strict_types=1);

$filters = is_array($filters ?? null) ? $filters : [];
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
                <h2 class="h5 mb-1">Familias cadastradas</h2>
                <p class="text-secondary mb-0">Busca por nome, CPF, bairro, cidade e status.</p>
            </div>
            <a class="btn btn-teal text-white" href="/families/create">Nova familia</a>
        </div>

        <form method="get" action="/families" class="row g-2">
            <div class="col-12 col-lg-4">
                <input type="text" name="q" class="form-control" placeholder="Nome, CPF, bairro ou cidade" value="<?= htmlspecialchars((string) ($filters['q'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-6 col-lg-2">
                <input type="text" name="city" class="form-control" placeholder="Cidade" value="<?= htmlspecialchars((string) ($filters['city'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-6 col-lg-2">
                <select name="status" class="form-select">
                    <option value="">Status</option>
                    <option value="ativo" <?= (($filters['status'] ?? '') === 'ativo') ? 'selected' : '' ?>>Ativo</option>
                    <option value="inativo" <?= (($filters['status'] ?? '') === 'inativo') ? 'selected' : '' ?>>Inativo</option>
                </select>
            </div>
            <div class="col-6 col-lg-2">
                <select name="documentation_status" class="form-select">
                    <option value="">Documentos</option>
                    <option value="ok" <?= (($filters['documentation_status'] ?? '') === 'ok') ? 'selected' : '' ?>>ok</option>
                    <option value="pendente" <?= (($filters['documentation_status'] ?? '') === 'pendente') ? 'selected' : '' ?>>pendente</option>
                    <option value="parcial" <?= (($filters['documentation_status'] ?? '') === 'parcial') ? 'selected' : '' ?>>parcial</option>
                </select>
            </div>
            <div class="col-6 col-lg-2 d-grid">
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
                    <th>Responsavel</th>
                    <th>CPF</th>
                    <th>Endereco</th>
                    <th>Renda</th>
                    <th>Docs</th>
                    <th>Visita</th>
                    <th>Status</th>
                    <th>Acoes</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($families)) : ?>
                <tr>
                    <td colspan="8" class="text-secondary p-4">Nenhuma familia encontrada.</td>
                </tr>
            <?php else : ?>
                <?php foreach ($families as $family) : ?>
                    <?php
                    $id = (int) ($family['id'] ?? 0);
                    $parts = array_filter([
                        (string) ($family['neighborhood'] ?? ''),
                        (string) ($family['city'] ?? ''),
                        (string) ($family['state'] ?? ''),
                    ]);
                    ?>
                    <tr>
                        <td>
                            <div class="fw-semibold"><?= htmlspecialchars((string) ($family['responsible_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                            <div class="small text-secondary"><?= htmlspecialchars((string) ($family['phone'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                        </td>
                        <td><?= htmlspecialchars((string) ($family['cpf_responsible'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($parts ? implode(' / ', $parts) : '-', ENT_QUOTES, 'UTF-8') ?></td>
                        <td>R$ <?= number_format((float) ($family['family_income_total'] ?? 0), 2, ',', '.') ?></td>
                        <td><span class="badge text-bg-light border"><?= htmlspecialchars((string) ($family['documentation_status'] ?? 'ok'), ENT_QUOTES, 'UTF-8') ?></span></td>
                        <td>
                            <?= ((int) ($family['needs_visit'] ?? 0) === 1)
                                ? '<span class="badge text-bg-warning">Pendente</span>'
                                : '<span class="badge text-bg-success">OK</span>' ?>
                        </td>
                        <td>
                            <?= ((int) ($family['is_active'] ?? 0) === 1)
                                ? '<span class="badge text-bg-success">Ativo</span>'
                                : '<span class="badge text-bg-danger">Inativo</span>' ?>
                        </td>
                        <td>
                            <a class="btn btn-sm btn-outline-secondary" href="/families/edit?id=<?= $id ?>">Editar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

