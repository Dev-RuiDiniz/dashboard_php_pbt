<?php
declare(strict_types=1);

$isEdit = ($mode ?? 'create') === 'edit';
$event = is_array($event ?? null) ? $event : [];
$statuses = is_array($statuses ?? null) ? $statuses : ['rascunho', 'aberto', 'concluido'];
?>
<div class="row justify-content-center">
    <div class="col-12 col-xl-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h2 class="h4 mb-1"><?= $isEdit ? 'Editar evento de entrega' : 'Novo evento de entrega' ?></h2>
                        <p class="text-secondary mb-0">Defina regras do evento: bloqueio mensal, limite de cestas e status.</p>
                    </div>
                    <a class="btn btn-outline-secondary" href="/delivery-events">Voltar</a>
                </div>

                <?php if (!empty($error)) : ?>
                    <div class="alert alert-danger border-0 shadow-sm"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>

                <form method="post" action="<?= $isEdit ? '/delivery-events/update?id=' . (int) ($event['id'] ?? 0) : '/delivery-events' ?>">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Nome do evento</label>
                            <input class="form-control" name="name" required value="<?= htmlspecialchars((string) ($event['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Data do evento</label>
                            <input type="date" class="form-control" name="event_date" required value="<?= htmlspecialchars((string) ($event['event_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status" required>
                                <?php foreach ($statuses as $status) : ?>
                                    <option value="<?= htmlspecialchars((string) $status, ENT_QUOTES, 'UTF-8') ?>" <?= ((string) ($event['status'] ?? 'rascunho') === (string) $status) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars((string) $status, ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Limite de cestas (opcional)</label>
                            <input type="number" min="1" class="form-control" name="max_baskets" placeholder="Sem limite" value="<?= htmlspecialchars((string) (($event['max_baskets'] ?? '') !== null ? (string) ($event['max_baskets'] ?? '') : ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="block_multiple_same_month" name="block_multiple_same_month" value="1" <?= ((int) ($event['block_multiple_same_month'] ?? 0) === 1) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="block_multiple_same_month">
                                    Bloquear multiplas entregas no mesmo mes para a mesma familia
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-teal text-white"><?= $isEdit ? 'Salvar alteracoes' : 'Criar evento' ?></button>
                        <a class="btn btn-outline-secondary" href="/delivery-events">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

