<?php
declare(strict_types=1);

$event = is_array($event ?? null) ? $event : [];
$deliveries = is_array($deliveries ?? null) ? $deliveries : [];
$deliveryFilters = is_array($deliveryFilters ?? null) ? $deliveryFilters : ['q' => '', 'status' => ''];
$summary = is_array($summary ?? null) ? $summary : [];
$generatedAt = (string) ($generatedAt ?? date('Y-m-d H:i:s'));
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Impressao - Lista Operacional</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; color: #111; }
        h1 { margin: 0 0 6px 0; font-size: 22px; }
        .meta { font-size: 13px; margin-bottom: 14px; }
        .summary { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 8px; margin-bottom: 14px; }
        .summary-item { border: 1px solid #d9d9d9; padding: 8px; border-radius: 6px; }
        .summary-label { font-size: 11px; text-transform: uppercase; color: #555; }
        .summary-value { font-size: 18px; font-weight: 700; margin-top: 3px; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; }
        th, td { border: 1px solid #d9d9d9; padding: 6px; text-align: left; vertical-align: top; }
        th { background: #f2f2f2; }
        .muted { color: #666; font-size: 12px; margin-top: 8px; }
        @media print {
            body { margin: 10px; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom:12px;">
        <button onclick="window.print()">Imprimir</button>
    </div>

    <h1>Lista operacional de entregas</h1>
    <div class="meta">
        Evento: <strong><?= htmlspecialchars((string) ($event['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong> |
        Data: <strong><?= htmlspecialchars((string) ($event['event_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong> |
        Status: <strong><?= htmlspecialchars((string) ($event['status'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong><br>
        Filtro status: <strong><?= htmlspecialchars((string) (($deliveryFilters['status'] ?? '') ?: 'todos'), ENT_QUOTES, 'UTF-8') ?></strong> |
        Busca: <strong><?= htmlspecialchars((string) (($deliveryFilters['q'] ?? '') ?: '-'), ENT_QUOTES, 'UTF-8') ?></strong> |
        Gerado em: <strong><?= htmlspecialchars($generatedAt, ENT_QUOTES, 'UTF-8') ?></strong>
    </div>

    <div class="summary">
        <div class="summary-item">
            <div class="summary-label">Registros</div>
            <div class="summary-value"><?= (int) ($summary['total_records'] ?? 0) ?></div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Nao veio</div>
            <div class="summary-value"><?= (int) ($summary['status_nao_veio'] ?? 0) ?></div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Presentes</div>
            <div class="summary-value"><?= (int) ($summary['status_presente'] ?? 0) ?></div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Cestas retiradas</div>
            <div class="summary-value"><?= (int) ($summary['withdrawn_quantity'] ?? 0) ?></div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Senha</th>
                <th>Convidado</th>
                <th>Documento</th>
                <th>Status</th>
                <th>Quantidade</th>
                <th>Assinatura</th>
                <th>Retirado em</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($deliveries)) : ?>
            <tr>
                <td colspan="7">Nenhum registro para os filtros informados.</td>
            </tr>
        <?php else : ?>
            <?php foreach ($deliveries as $item) : ?>
                <?php $name = (string) (($item['family_name'] ?? '') ?: (($item['person_full_name'] ?? '') ?: ($item['person_social_name'] ?? ''))); ?>
                <tr>
                    <td><?= (int) ($item['ticket_number'] ?? 0) ?></td>
                    <td><?= htmlspecialchars($name !== '' ? $name : 'Sem identificacao', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars((string) (($item['document_id'] ?? '') ?: '-'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars((string) ($item['status'] ?? 'nao_veio'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= (int) ($item['quantity'] ?? 1) ?></td>
                    <td><?= htmlspecialchars((string) (($item['signature_name'] ?? '') ?: '-'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars((string) (($item['delivered_at'] ?? '') ?: '-'), ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>

    <div class="muted">Documento operacional para conferencia e assinatura.</div>
</body>
</html>
