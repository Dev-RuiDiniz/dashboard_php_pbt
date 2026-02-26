<?php
declare(strict_types=1);

$isEdit = ($mode ?? 'create') === 'edit';
$child = is_array($child ?? null) ? $child : [];
$families = is_array($families ?? null) ? $families : [];
?>
<div class="row justify-content-center">
    <div class="col-12 col-xl-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h2 class="h4 mb-1"><?= $isEdit ? 'Editar crianca' : 'Nova crianca' ?></h2>
                        <p class="text-secondary mb-0">Cadastro vinculado a familia.</p>
                    </div>
                    <a class="btn btn-outline-secondary" href="/children">Voltar</a>
                </div>

                <?php if (!empty($error)) : ?>
                    <div class="alert alert-danger border-0 shadow-sm"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>

                <form method="post" action="<?= $isEdit ? '/children/update?id=' . (int) ($child['id'] ?? 0) : '/children' ?>">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Familia vinculada</label>
                            <select class="form-select" name="family_id" required>
                                <option value="0">Selecione</option>
                                <?php foreach ($families as $family) : ?>
                                    <?php $fid = (int) ($family['id'] ?? 0); ?>
                                    <option value="<?= $fid ?>" <?= ((int) ($child['family_id'] ?? 0) === $fid) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars((string) ($family['responsible_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Nome da crianca</label>
                            <input class="form-control" name="name" required value="<?= htmlspecialchars((string) ($child['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label">Data de nascimento</label>
                            <input type="date" class="form-control" name="birth_date" value="<?= htmlspecialchars((string) ($child['birth_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label">Idade aprox.</label>
                            <input type="number" min="0" class="form-control" name="age_years" value="<?= htmlspecialchars((string) (($child['age_years'] ?? '') !== null ? (string) ($child['age_years'] ?? '') : ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Parentesco</label>
                            <input class="form-control" name="relationship" value="<?= htmlspecialchars((string) ($child['relationship'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Observacoes</label>
                            <textarea class="form-control" name="notes" rows="3"><?= htmlspecialchars((string) ($child['notes'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2 mt-4">
                        <button type="submit" class="btn btn-teal text-white"><?= $isEdit ? 'Salvar alteracoes' : 'Cadastrar crianca' ?></button>
                        <a class="btn btn-outline-secondary" href="/children">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

