<?php
declare(strict_types=1);

$isEdit = ($mode ?? 'create') === 'edit';
$equipment = is_array($equipment ?? null) ? $equipment : [];
$types = is_array($types ?? null) ? $types : [];
$conditions = is_array($conditions ?? null) ? $conditions : [];
$statuses = is_array($statuses ?? null) ? $statuses : [];
?>
<div class="row justify-content-center">
    <div class="col-12 col-xl-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h2 class="h4 mb-1"><?= $isEdit ? 'Editar equipamento' : 'Novo equipamento' ?></h2>
                        <p class="text-secondary mb-0">Codigo gerado automaticamente por tipo.</p>
                    </div>
                    <a class="btn btn-outline-secondary" href="/equipment">Voltar</a>
                </div>

                <?php if (!empty($error)) : ?>
                    <div class="alert alert-danger border-0 shadow-sm"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>

                <form method="post" action="<?= $isEdit ? '/equipment/update?id=' . (int) ($equipment['id'] ?? 0) : '/equipment' ?>">
                    <?php if ($isEdit) : ?>
                        <div class="mb-3">
                            <label class="form-label">Codigo</label>
                            <input class="form-control" value="<?= htmlspecialchars((string) ($equipmentCode ?? ($equipment['code'] ?? '')), ENT_QUOTES, 'UTF-8') ?>" disabled>
                        </div>
                    <?php endif; ?>

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Tipo</label>
                            <select class="form-select" name="type" required>
                                <?php foreach ($types as $type) : ?>
                                    <option value="<?= htmlspecialchars((string) $type, ENT_QUOTES, 'UTF-8') ?>" <?= ((string) ($equipment['type'] ?? '') === (string) $type) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars(ucwords(str_replace('_', ' ', (string) $type)), ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Estado de conservacao</label>
                            <select class="form-select" name="condition_state" required>
                                <?php foreach ($conditions as $condition) : ?>
                                    <option value="<?= htmlspecialchars((string) $condition, ENT_QUOTES, 'UTF-8') ?>" <?= ((string) ($equipment['condition_state'] ?? '') === (string) $condition) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars(ucwords(str_replace('_', ' ', (string) $condition)), ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status" required>
                                <?php foreach ($statuses as $status) : ?>
                                    <option value="<?= htmlspecialchars((string) $status, ENT_QUOTES, 'UTF-8') ?>" <?= ((string) ($equipment['status'] ?? '') === (string) $status) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars(ucwords(str_replace('_', ' ', (string) $status)), ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Observacoes</label>
                            <textarea class="form-control" name="notes" rows="4"><?= htmlspecialchars((string) ($equipment['notes'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2 mt-4">
                        <button type="submit" class="btn btn-teal text-white"><?= $isEdit ? 'Salvar alteracoes' : 'Cadastrar equipamento' ?></button>
                        <a class="btn btn-outline-secondary" href="/equipment">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

