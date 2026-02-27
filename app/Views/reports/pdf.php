<?php
declare(strict_types=1);

$filters = is_array($filters ?? null) ? $filters : [];
$reportData = is_array($reportData ?? null) ? $reportData : [];
$families = is_array($reportData['families'] ?? null) ? $reportData['families'] : ['summary' => [], 'items' => []];
$baskets = is_array($reportData['baskets'] ?? null) ? $reportData['baskets'] : ['summary' => [], 'items' => []];
$children = is_array($reportData['children'] ?? null) ? $reportData['children'] : ['summary' => [], 'items' => []];
$referrals = is_array($reportData['referrals'] ?? null) ? $reportData['referrals'] : ['summary' => [], 'items' => []];
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
        h1 { font-size: 18px; margin: 0 0 8px; }
        h2 { font-size: 14px; margin: 16px 0 6px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        th, td { border: 1px solid #ddd; padding: 4px 6px; text-align: left; }
        .muted { color: #555; }
    </style>
</head>
<body>
    <h1><?= htmlspecialchars((string) ($appName ?? 'Dashboard PHP PBT'), ENT_QUOTES, 'UTF-8') ?> - Relatorio Mensal</h1>
    <div class="muted">
        Periodo: <?= htmlspecialchars((string) ($filters['period_start'] ?? ''), ENT_QUOTES, 'UTF-8') ?> ate
        <?= htmlspecialchars((string) ($filters['period_end'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
        | Status: <?= htmlspecialchars((string) (($filters['status'] ?? '') !== '' ? $filters['status'] : 'todos'), ENT_QUOTES, 'UTF-8') ?>
        | Bairro: <?= htmlspecialchars((string) (($filters['neighborhood'] ?? '') !== '' ? $filters['neighborhood'] : 'todos'), ENT_QUOTES, 'UTF-8') ?>
    </div>

    <h2>Resumo</h2>
    <table>
        <tr><th>Familias</th><th>Cestas</th><th>Criancas</th><th>Encaminhamentos</th></tr>
        <tr>
            <td><?= (int) (($families['summary']['total_families'] ?? 0)) ?></td>
            <td><?= (int) (($baskets['summary']['total_baskets'] ?? 0)) ?></td>
            <td><?= (int) (($children['summary']['total_children'] ?? 0)) ?></td>
            <td><?= (int) (($referrals['summary']['total_referrals'] ?? 0)) ?></td>
        </tr>
    </table>

    <h2>Familias (amostra)</h2>
    <table>
        <tr><th>Responsavel</th><th>Bairro</th><th>Status</th></tr>
        <?php foreach (array_slice((array) ($families['items'] ?? []), 0, 12) as $item) : ?>
            <tr>
                <td><?= htmlspecialchars((string) ($item['responsible_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string) ($item['neighborhood'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= ((int) ($item['is_active'] ?? 0) === 1) ? 'ativo' : 'inativo' ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <h2>Cestas (amostra)</h2>
    <table>
        <tr><th>Evento</th><th>Familia</th><th>Qtd</th><th>Status</th></tr>
        <?php foreach (array_slice((array) ($baskets['items'] ?? []), 0, 12) as $item) : ?>
            <tr>
                <td><?= htmlspecialchars((string) ($item['event_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string) ($item['family_name'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= (int) ($item['quantity'] ?? 0) ?></td>
                <td><?= htmlspecialchars((string) ($item['status'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <h2>Criancas (amostra)</h2>
    <table>
        <tr><th>Crianca</th><th>Familia</th><th>Bairro</th></tr>
        <?php foreach (array_slice((array) ($children['items'] ?? []), 0, 12) as $item) : ?>
            <tr>
                <td><?= htmlspecialchars((string) ($item['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string) ($item['family_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string) ($item['neighborhood'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <h2>Encaminhamentos (amostra)</h2>
    <table>
        <tr><th>Tipo</th><th>Pessoa</th><th>Status</th><th>Data</th></tr>
        <?php foreach (array_slice((array) ($referrals['items'] ?? []), 0, 12) as $item) : ?>
            <tr>
                <td><?= htmlspecialchars((string) ($item['referral_type'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string) ($item['person_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string) ($item['status'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string) ($item['referral_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>

