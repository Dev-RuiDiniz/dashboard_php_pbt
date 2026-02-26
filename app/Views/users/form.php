<?php
declare(strict_types=1);

$isEdit = ($mode ?? 'create') === 'edit';
$userData = is_array($user ?? null) ? $user : [];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isEdit ? 'Editar' : 'Novo' ?> Usuario - <?= htmlspecialchars($appName ?? 'Dashboard PHP PBT', ENT_QUOTES, 'UTF-8') ?></title>
    <style>
        body { margin:0; font-family: Arial, sans-serif; background:#f8fafc; color:#0f172a; }
        .wrap { padding:1.25rem; display:flex; justify-content:center; }
        .card { width:100%; max-width:700px; background:#fff; border-radius:12px; padding:1.25rem; box-shadow:0 8px 20px rgba(0,0,0,.06); }
        h1 { margin-top:0; }
        .grid { display:grid; grid-template-columns: 1fr 1fr; gap:1rem; }
        .field { margin-bottom:.9rem; }
        label { display:block; font-weight:600; margin-bottom:.35rem; }
        input, select { width:100%; box-sizing:border-box; padding:.7rem; border:1px solid #cbd5e1; border-radius:8px; }
        .actions { display:flex; gap:.6rem; margin-top:1rem; flex-wrap:wrap; }
        .btn, button { border:0; border-radius:8px; padding:.65rem .9rem; cursor:pointer; font-weight:600; text-decoration:none; display:inline-block; }
        .btn-primary { background:#0f766e; color:#fff; }
        .btn-muted { background:#e2e8f0; color:#0f172a; }
        .hint { color:#64748b; font-size:.9rem; }
        .alert { border-radius:8px; padding:.75rem; margin-bottom:1rem; }
        .alert.error { background:#fee2e2; color:#991b1b; }
    </style>
</head>
<body>
    <main class="wrap">
        <div class="card">
            <h1><?= $isEdit ? 'Editar usuario' : 'Novo usuario' ?></h1>
            <p class="hint">Perfis disponiveis: admin, voluntario, pastoral, viewer.</p>

            <?php if (!empty($error)) : ?>
                <div class="alert error"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <form method="post" action="<?= $isEdit ? '/users/update?id=' . (int) ($userData['id'] ?? 0) : '/users' ?>">
                <div class="grid">
                    <div class="field">
                        <label for="name">Nome</label>
                        <input id="name" name="name" required value="<?= htmlspecialchars((string) ($userData['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                    <div class="field">
                        <label for="email">E-mail</label>
                        <input id="email" type="email" name="email" required value="<?= htmlspecialchars((string) ($userData['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                    <div class="field">
                        <label for="role">Perfil</label>
                        <select id="role" name="role" required>
                            <?php foreach (($roles ?? []) as $role) : ?>
                                <option value="<?= htmlspecialchars((string) $role, ENT_QUOTES, 'UTF-8') ?>" <?= ((string) ($userData['role'] ?? '') === (string) $role) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars((string) $role, ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="field">
                        <label for="password"><?= $isEdit ? 'Nova senha (opcional)' : 'Senha' ?></label>
                        <input id="password" type="password" name="password" <?= $isEdit ? '' : 'required' ?>>
                    </div>
                </div>

                <div class="field">
                    <label>
                        <input type="checkbox" name="is_active" value="1" style="width:auto;" <?= ((int) ($userData['is_active'] ?? 0) === 1) ? 'checked' : '' ?>>
                        Usuario ativo
                    </label>
                </div>

                <div class="actions">
                    <button type="submit" class="btn-primary"><?= $isEdit ? 'Salvar alteracoes' : 'Criar usuario' ?></button>
                    <a class="btn btn-muted" href="/users">Cancelar</a>
                </div>
            </form>
        </div>
    </main>
</body>
</html>

