<?php
declare(strict_types=1);

$isEdit = ($mode ?? 'create') === 'edit';
$visit = is_array($visit ?? null) ? $visit : [];
$families = is_array($families ?? null) ? $families : [];
$people = is_array($people ?? null) ? $people : [];
$statuses = is_array($statuses ?? null) ? $statuses : [];
?>
<div class="row justify-content-center">
    <div class="col-12 col-xl-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h2 class="h4 mb-1"><?= $isEdit ? 'Editar visita' : 'Solicitar visita' ?></h2>
                        <p class="text-secondary mb-0">Selecione familia ou pessoa e defina agendamento.</p>
                    </div>
                    <a class="btn btn-outline-secondary" href="/visits">Voltar</a>
                </div>

                <?php if (!empty($error)) : ?>
                    <div class="alert alert-danger border-0 shadow-sm"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>

                <form method="post" action="<?= $isEdit ? '/visits/update?id=' . (int) ($visit['id'] ?? 0) : '/visits' ?>">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Familia (opcional)</label>
                            <select class="form-select" name="family_id">
                                <option value="0">Selecione</option>
                                <?php foreach ($families as $family) : ?>
                                    <?php $fid = (int) ($family['id'] ?? 0); ?>
                                    <option value="<?= $fid ?>" <?= ((int) ($visit['family_id'] ?? 0) === $fid) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars((string) ($family['responsible_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Pessoa (opcional)</label>
                            <select class="form-select" name="person_id">
                                <option value="0">Selecione</option>
                                <?php foreach ($people as $person) : ?>
                                    <?php $pid = (int) ($person['id'] ?? 0); ?>
                                    <option value="<?= $pid ?>" <?= ((int) ($visit['person_id'] ?? 0) === $pid) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars((string) (($person['full_name'] ?? '') !== '' ? $person['full_name'] : ($person['social_name'] ?? '')), ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Data agendada</label>
                            <input type="date" class="form-control" name="scheduled_date" value="<?= htmlspecialchars((string) ($visit['scheduled_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <?php foreach ($statuses as $status) : ?>
                                    <?php if (!$isEdit && $status === 'concluida') {
                                        continue;
                                    } ?>
                                    <option value="<?= htmlspecialchars((string) $status, ENT_QUOTES, 'UTF-8') ?>" <?= ((string) ($visit['status'] ?? 'pendente') === (string) $status) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars(ucwords((string) $status), ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Observacoes</label>
                            <textarea class="form-control" name="notes" rows="4"><?= htmlspecialchars((string) ($visit['notes'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2 mt-4">
                        <button type="submit" class="btn btn-teal text-white"><?= $isEdit ? 'Salvar alteracoes' : 'Solicitar visita' ?></button>
                        <a class="btn btn-outline-secondary" href="/visits">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

