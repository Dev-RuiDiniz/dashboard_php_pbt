<?php
declare(strict_types=1);

$event = is_array($event ?? null) ? $event : [];
$deliveries = is_array($deliveries ?? null) ? $deliveries : [];
$families = is_array($families ?? null) ? $families : [];
$people = is_array($people ?? null) ? $people : [];
$deliveryForm = is_array($deliveryForm ?? null) ? $deliveryForm : [];
$eventId = (int) ($event['id'] ?? 0);
?>

<?php if (!empty($success)) : ?>
    <div class="alert alert-success shadow-sm border-0"><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>
<?php if (!empty($error)) : ?>
    <div class="alert alert-danger shadow-sm border-0"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<div class="d-flex flex-wrap gap-2 mb-3">
    <a class="btn btn-outline-secondary" href="/delivery-events">Voltar aos eventos</a>
    <a class="btn btn-outline-primary" href="/delivery-events/edit?id=<?= $eventId ?>">Editar evento</a>
</div>

<div class="row g-3 mb-3">
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card metric-card h-100"><div class="card-body">
            <div class="small text-secondary text-uppercase">Evento</div>
            <div class="fw-semibold"><?= htmlspecialchars((string) ($event['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
            <div class="small text-secondary"><?= htmlspecialchars((string) ($event['event_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
        </div></div>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card metric-card h-100"><div class="card-body">
            <div class="small text-secondary text-uppercase">Status</div>
            <div class="metric-value"><?= htmlspecialchars((string) ($event['status'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
        </div></div>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card metric-card h-100"><div class="card-body">
            <div class="small text-secondary text-uppercase">Bloqueio mensal</div>
            <div class="metric-value"><?= ((int) ($event['block_multiple_same_month'] ?? 0) === 1) ? 'ON' : 'OFF' ?></div>
        </div></div>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card metric-card h-100"><div class="card-body">
            <div class="small text-secondary text-uppercase">Limite cestas</div>
            <div class="metric-value"><?= ($event['max_baskets'] ?? null) !== null ? (int) $event['max_baskets'] : '∞' ?></div>
        </div></div>
    </div>
</div>

<div class="row g-3">
    <div class="col-12 col-xl-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h2 class="h5 mb-3">Geracao de convidados (manual)</h2>
                <form method="post" action="/delivery-events/deliveries?event_id=<?= $eventId ?>">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Tipo de convidado</label>
                            <select class="form-select" name="target_type">
                                <option value="family" <?= (($deliveryForm['target_type'] ?? 'family') === 'family') ? 'selected' : '' ?>>Familia</option>
                                <option value="person" <?= (($deliveryForm['target_type'] ?? '') === 'person') ? 'selected' : '' ?>>Pessoa acompanhada</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Familia (quando tipo = familia)</label>
                            <select class="form-select" name="family_id">
                                <option value="0">Selecione</option>
                                <?php foreach ($families as $family) : ?>
                                    <?php $fid = (int) ($family['id'] ?? 0); ?>
                                    <option value="<?= $fid ?>" <?= ((int) ($deliveryForm['family_id'] ?? 0) === $fid) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars((string) ($family['responsible_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Pessoa acompanhada (quando tipo = pessoa)</label>
                            <select class="form-select" name="person_id">
                                <option value="0">Selecione</option>
                                <?php foreach ($people as $person) : ?>
                                    <?php $pid = (int) ($person['id'] ?? 0); $pname = (string) (($person['full_name'] ?? '') ?: ($person['social_name'] ?? 'Sem identificacao')); ?>
                                    <option value="<?= $pid ?>" <?= ((int) ($deliveryForm['person_id'] ?? 0) === $pid) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($pname, ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Quantidade</label>
                            <input type="number" min="1" class="form-control" name="quantity" value="<?= htmlspecialchars((string) ($deliveryForm['quantity'] ?? 1), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="col-12 col-md-8">
                            <label class="form-label">Observacoes</label>
                            <input class="form-control" name="observations" value="<?= htmlspecialchars((string) ($deliveryForm['observations'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    </div>
                    <div class="mt-3 d-flex gap-2">
                        <button type="submit" class="btn btn-teal text-white">Adicionar na lista</button>
                    </div>
                </form>

                <div class="alert alert-light border small mt-3 mb-0">
                    A senha (ticket) e gerada automaticamente em sequencia por evento e fica imutavel.
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h2 class="h5 mb-3">Lista operacional</h2>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Senha</th>
                                <th>Convidado</th>
                                <th>Documento</th>
                                <th>Status</th>
                                <th>Qtd</th>
                                <th>Operacao</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($deliveries)) : ?>
                            <tr><td colspan="6" class="text-secondary">Nenhum convidado na lista operacional.</td></tr>
                        <?php else : ?>
                            <?php foreach ($deliveries as $d) : ?>
                                <?php
                                $deliveryId = (int) ($d['id'] ?? 0);
                                $status = (string) ($d['status'] ?? 'nao_veio');
                                $name = (string) (($d['family_name'] ?? '') ?: (($d['person_full_name'] ?? '') ?: ($d['person_social_name'] ?? '')));
                                ?>
                                <tr>
                                    <td><span class="badge text-bg-dark"><?= (int) ($d['ticket_number'] ?? 0) ?></span></td>
                                    <td>
                                        <div class="fw-semibold"><?= htmlspecialchars($name !== '' ? $name : 'Sem identificacao', ENT_QUOTES, 'UTF-8') ?></div>
                                        <?php if (!empty($d['observations'])) : ?>
                                            <div class="small text-secondary"><?= htmlspecialchars((string) $d['observations'], ENT_QUOTES, 'UTF-8') ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars((string) (($d['document_id'] ?? '') ?: '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>
                                        <span class="badge text-bg-light border"><?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?></span>
                                        <?php if ($status === 'retirou') : ?>
                                            <div class="small text-secondary mt-1">
                                                <?= htmlspecialchars((string) (($d['signature_name'] ?? '') ?: '-'), ENT_QUOTES, 'UTF-8') ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= (int) ($d['quantity'] ?? 1) ?></td>
                                    <td>
                                        <form method="post" action="/delivery-events/deliveries/status?id=<?= $deliveryId ?>&event_id=<?= $eventId ?>" class="vstack gap-2">
                                            <input type="hidden" name="target_status" value="presente">
                                            <?php if ($status === 'nao_veio') : ?>
                                                <button type="submit" class="btn btn-sm btn-outline-primary">Marcar presente</button>
                                            <?php endif; ?>
                                        </form>

                                        <?php if ($status === 'presente') : ?>
                                            <form method="post" action="/delivery-events/deliveries/status?id=<?= $deliveryId ?>&event_id=<?= $eventId ?>" class="vstack gap-2 mt-2">
                                                <input type="hidden" name="target_status" value="retirou">
                                                <input type="text" name="signature_name" class="form-control form-control-sm" placeholder="Assinatura (nome digitado)" required>
                                                <button type="submit" class="btn btn-sm btn-success">Marcar retirou</button>
                                            </form>
                                        <?php elseif ($status === 'retirou') : ?>
                                            <div class="small text-secondary">Retirado em <?= htmlspecialchars((string) (($d['delivered_at'] ?? '') ?: '-'), ENT_QUOTES, 'UTF-8') ?></div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

