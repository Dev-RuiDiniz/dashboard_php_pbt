<?php
declare(strict_types=1);

$auth = is_array($authUser ?? null) ? $authUser : [];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios - <?= htmlspecialchars($appName ?? 'Dashboard PHP PBT', ENT_QUOTES, 'UTF-8') ?></title>
    <style>
        body { margin: 0; font-family: Arial, sans-serif; background: #f8fafc; color: #0f172a; }
        .topbar { display:flex; justify-content:space-between; align-items:center; padding: 1rem 1.25rem; background:#0f172a; color:#fff; gap:1rem; }
        .topbar a { color:#fff; text-decoration:none; opacity:.95; }
        .wrap { padding:1.25rem; }
        .card { background:#fff; border-radius:12px; padding:1.25rem; box-shadow:0 8px 20px rgba(0,0,0,.06); }
        .actions { display:flex; gap:.5rem; flex-wrap:wrap; margin-bottom:1rem; }
        .btn, button { border:0; border-radius:8px; padding:.55rem .8rem; cursor:pointer; font-weight:600; text-decoration:none; display:inline-block; }
        .btn-primary { background:#0f766e; color:#fff; }
        .btn-muted { background:#e2e8f0; color:#0f172a; }
        .btn-danger { background:#dc2626; color:#fff; }
        .btn-warn { background:#b45309; color:#fff; }
        table { width:100%; border-collapse: collapse; }
        th, td { padding:.75rem; border-bottom:1px solid #e2e8f0; text-align:left; vertical-align:top; }
        th { font-size:.9rem; color:#475569; }
        .badge { display:inline-block; padding:.2rem .45rem; border-radius:999px; font-size:.8rem; background:#e2e8f0; }
        .ok { background:#dcfce7; color:#166534; }
        .off { background:#fee2e2; color:#991b1b; }
        .alert { border-radius: 8px; padding: .75rem; margin-bottom: 1rem; }
        .alert.error { background: #fee2e2; color: #991b1b; }
        .alert.success { background: #dcfce7; color: #166534; }
        .row-actions { display:flex; gap:.35rem; flex-wrap:wrap; }
        form.inline { display:inline; margin:0; }
        code { background:#e2e8f0; padding:.1rem .25rem; border-radius:4px; }
    </style>
</head>
<body>
    <header class="topbar">
        <div>
            <strong><?= htmlspecialchars($appName ?? 'Dashboard PHP PBT', ENT_QUOTES, 'UTF-8') ?></strong>
            <span style="opacity:.8;"> | Usuarios (admin)</span>
        </div>
        <div style="display:flex; gap:.75rem; align-items:center;">
            <a href="/dashboard">Dashboard</a>
            <form class="inline" method="post" action="/logout">
                <button type="submit" class="btn-danger">Sair</button>
            </form>
        </div>
    </header>

    <main class="wrap">
        <div class="card">
            <?php if (!empty($success)) : ?>
                <div class="alert success"><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
            <?php if (!empty($error)) : ?>
                <div class="alert error"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <div class="actions">
                <a class="btn btn-primary" href="/users/create">Novo usuario</a>
                <a class="btn btn-muted" href="/dashboard">Voltar ao dashboard</a>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>Perfil</th>
                        <th>Status</th>
                        <th>Acoes</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($users)) : ?>
                    <tr>
                        <td colspan="6">Nenhum usuario encontrado ou falha de conexao.</td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($users as $row) : ?>
                        <?php
                        $id = (int) ($row['id'] ?? 0);
                        $isActive = (int) ($row['is_active'] ?? 0) === 1;
                        $isSelf = (int) ($auth['id'] ?? 0) === $id;
                        ?>
                        <tr>
                            <td><?= $id ?></td>
                            <td><?= htmlspecialchars((string) ($row['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) ($row['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><code><?= htmlspecialchars((string) ($row['role'] ?? ''), ENT_QUOTES, 'UTF-8') ?></code></td>
                            <td>
                                <span class="badge <?= $isActive ? 'ok' : 'off' ?>">
                                    <?= $isActive ? 'ativo' : 'inativo' ?>
                                </span>
                            </td>
                            <td>
                                <div class="row-actions">
                                    <a class="btn btn-muted" href="/users/edit?id=<?= $id ?>">Editar</a>
                                    <?php if (!$isSelf) : ?>
                                        <form class="inline" method="post" action="/users/toggle?id=<?= $id ?>">
                                            <button type="submit" class="<?= $isActive ? 'btn-warn' : 'btn-primary' ?>">
                                                <?= $isActive ? 'Desativar' : 'Ativar' ?>
                                            </button>
                                        </form>
                                    <?php else : ?>
                                        <span style="color:#64748b;">(voce)</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>

