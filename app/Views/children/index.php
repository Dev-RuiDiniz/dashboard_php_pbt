<?php
declare(strict_types=1);

$filters = is_array($filters ?? null) ? $filters : [];
$families = is_array($families ?? null) ? $families : [];
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
                <h2 class="h5 mb-1">Lista de criancas</h2>
                <p class="text-secondary mb-0">Filtro por nome da crianca, familia e cidade.</p>
            </div>
            <a class="btn btn-teal text-white" href="/children/create">Nova crianca</a>
        </div>

        <form method="get" action="/children" class="row g-2">
            <div class="col-12 col-lg-4">
                <input type="text" class="form-control" name="q" placeholder="Nome da crianca ou responsavel" value="<?= htmlspecialchars((string) ($filters['q'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-12 col-lg-4">
                <select class="form-select" name="family_id">
                    <option value="0">Familia (todas)</option>
                    <?php foreach ($families as $family) : ?>
                        <?php $fid = (int) ($family['id'] ?? 0); ?>
                        <option value="<?= $fid ?>" <?= ((int) ($filters['family_id'] ?? 0) === $fid) ? 'selected' : '' ?>>
                            <?= htmlspecialchars((string) ($family['responsible_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-lg-2">
                <input type="text" class="form-control" name="city" placeholder="Cidade" value="<?= htmlspecialchars((string) ($filters['city'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
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
                    <th>Crianca</th>
                    <th>Idade/Data</th>
                    <th>Parentesco</th>
                    <th>Familia</th>
                    <th>Cidade</th>
                    <th>Acoes</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($children)) : ?>
                <tr>
                    <td colspan="6" class="text-secondary p-4">Nenhuma crianca encontrada.</td>
                </tr>
            <?php else : ?>
                <?php foreach ($children as $child) : ?>
                    <?php $childId = (int) ($child['id'] ?? 0); $familyId = (int) ($child['family_id'] ?? 0); ?>
                    <tr>
                        <td>
                            <div class="fw-semibold"><?= htmlspecialchars((string) ($child['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                            <?php if (!empty($child['notes'])) : ?>
                                <div class="small text-secondary"><?= htmlspecialchars((string) $child['notes'], ENT_QUOTES, 'UTF-8') ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($child['birth_date'])) : ?>
                                <div><?= htmlspecialchars((string) $child['birth_date'], ENT_QUOTES, 'UTF-8') ?></div>
                            <?php endif; ?>
                            <div class="small text-secondary">idade aprox.: <?= htmlspecialchars((string) (($child['age_years'] ?? '') !== null ? (string) ($child['age_years'] ?? '') : '-'), ENT_QUOTES, 'UTF-8') ?></div>
                        </td>
                        <td><?= htmlspecialchars((string) ($child['relationship'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <a href="/families/show?id=<?= $familyId ?>" class="text-decoration-none">
                                <?= htmlspecialchars((string) ($child['responsible_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                            </a>
                        </td>
                        <td><?= htmlspecialchars((string) ($child['city'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <div class="d-flex flex-wrap gap-2">
                                <a class="btn btn-sm btn-outline-secondary" href="/children/edit?id=<?= $childId ?>">Editar</a>
                                <form method="post" action="/children/delete?id=<?= $childId ?>" class="m-0" onsubmit="return confirm('Remover crianca?');">
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

