<?php
declare(strict_types=1);

$userName = is_array($user ?? null) ? (string) ($user['name'] ?? 'Usuario') : 'Usuario';
$userRole = is_array($user ?? null) ? (string) ($user['role'] ?? '-') : '-';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?= htmlspecialchars($appName ?? 'Dashboard PHP PBT', ENT_QUOTES, 'UTF-8') ?></title>
    <style>
        body { margin: 0; font-family: Arial, sans-serif; background: #f8fafc; color: #0f172a; }
        .topbar { display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.25rem; background: #0f172a; color: #fff; }
        .wrap { padding: 1.25rem; }
        .card { background: #fff; border-radius: 12px; padding: 1.25rem; box-shadow: 0 8px 20px rgba(0,0,0,.06); max-width: 900px; }
        .alert { border-radius: 8px; padding: .75rem; margin-bottom: 1rem; background: #dcfce7; color: #166534; }
        .meta { color: #475569; }
        button { background: #dc2626; color: #fff; border: 0; border-radius: 8px; padding: .6rem .9rem; cursor: pointer; font-weight: 600; }
        code { background: #e2e8f0; padding: .1rem .25rem; border-radius: 4px; }
    </style>
</head>
<body>
    <header class="topbar">
        <div>
            <strong><?= htmlspecialchars($appName ?? 'Dashboard PHP PBT', ENT_QUOTES, 'UTF-8') ?></strong>
        </div>
        <form method="post" action="/logout">
            <button type="submit">Sair</button>
        </form>
    </header>

    <main class="wrap">
        <div class="card">
            <?php if (!empty($success)) : ?>
                <div class="alert"><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <h1>Dashboard (protegido)</h1>
            <p class="meta">Sessao autenticada com sucesso.</p>
            <p>Usuario: <strong><?= htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') ?></strong></p>
            <p>Perfil: <code><?= htmlspecialchars($userRole, ENT_QUOTES, 'UTF-8') ?></code></p>
            <p class="meta">Sprint 2 concluida: login, sessao, logout e middleware de autenticacao.</p>
        </div>
    </main>
</body>
</html>
