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
                <h2 class="h5 mb-1">Pessoas acompanhadas</h2>
                <p class="text-secondary mb-0">Cadastro base para ficha social. Dados incompletos sao permitidos quando necessario.</p>
            </div>
            <a class="btn btn-teal text-white" href="/people/create">Nova pessoa</a>
        </div>

        <form method="get" action="/people" class="row g-2">
            <div class="col-12 col-lg-6">
                <input type="text" class="form-control" name="q" placeholder="Nome, nome social, CPF, RG ou local" value="<?= htmlspecialchars((string) ($filters['q'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-6 col-lg-2">
                <select class="form-select" name="is_homeless">
                    <option value="">Situacao de rua</option>
                    <option value="1" <?= (($filters['is_homeless'] ?? '') === '1') ? 'selected' : '' ?>>Sim</option>
                    <option value="0" <?= (($filters['is_homeless'] ?? '') === '0') ? 'selected' : '' ?>>Nao</option>
                </select>
            </div>
            <div class="col-6 col-lg-2">
                <select class="form-select" name="work_interest">
                    <option value="">Interesse trabalho</option>
                    <option value="1" <?= (($filters['work_interest'] ?? '') === '1') ? 'selected' : '' ?>>Sim</option>
                    <option value="0" <?= (($filters['work_interest'] ?? '') === '0') ? 'selected' : '' ?>>Nao</option>
                </select>
            </div>
            <div class="col-12 col-lg-2 d-grid">
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
                    <th>Identificacao</th>
                    <th>Documento</th>
                    <th>Situacao</th>
                    <th>Trabalho</th>
                    <th>Ultima atualizacao</th>
                    <th>Acoes</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($people)) : ?>
                <tr>
                    <td colspan="6" class="text-secondary p-4">Nenhuma pessoa encontrada.</td>
                </tr>
            <?php else : ?>
                <?php foreach ($people as $person) : ?>
                    <?php $id = (int) ($person['id'] ?? 0); ?>
                    <tr>
                        <td>
                            <div class="fw-semibold"><?= htmlspecialchars((string) (($person['full_name'] ?? '') ?: ($person['social_name'] ?? 'Sem identificacao')), ENT_QUOTES, 'UTF-8') ?></div>
                            <div class="small text-secondary">
                                nome social: <?= htmlspecialchars((string) (($person['social_name'] ?? '') ?: '-'), ENT_QUOTES, 'UTF-8') ?>
                            </div>
                        </td>
                        <td>
                            <div>CPF: <?= htmlspecialchars((string) (($person['cpf'] ?? '') ?: '-'), ENT_QUOTES, 'UTF-8') ?></div>
                            <div class="small text-secondary">RG: <?= htmlspecialchars((string) (($person['rg'] ?? '') ?: '-'), ENT_QUOTES, 'UTF-8') ?></div>
                        </td>
                        <td>
                            <?php if ((int) ($person['is_homeless'] ?? 0) === 1) : ?>
                                <span class="badge text-bg-warning">Situacao de rua</span>
                                <div class="small text-secondary mt-1"><?= htmlspecialchars((string) (($person['homeless_time'] ?? '') ?: '-'), ENT_QUOTES, 'UTF-8') ?></div>
                            <?php else : ?>
                                <span class="badge text-bg-success">Acompanhamento geral</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= ((int) ($person['work_interest'] ?? 0) === 1)
                                ? '<span class="badge text-bg-success">Interesse</span>'
                                : '<span class="badge text-bg-light border">Sem info</span>' ?>
                        </td>
                        <td><?= htmlspecialchars((string) (($person['updated_at'] ?? '') ?: ($person['created_at'] ?? '-')), ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <a class="btn btn-sm btn-outline-secondary" href="/people/edit?id=<?= $id ?>">Editar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

