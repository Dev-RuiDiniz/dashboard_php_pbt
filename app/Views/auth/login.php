<?php
declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= htmlspecialchars($appName ?? 'Dashboard PHP PBT', ENT_QUOTES, 'UTF-8') ?></title>
    <style>
        body { margin: 0; font-family: Arial, sans-serif; background: #f0f4f8; color: #1f2937; }
        .wrap { min-height: 100vh; display: grid; place-items: center; padding: 1rem; }
        .card { width: 100%; max-width: 420px; background: #fff; border-radius: 12px; padding: 1.5rem; box-shadow: 0 12px 30px rgba(0,0,0,.08); }
        h1 { margin: 0 0 .25rem; font-size: 1.25rem; }
        p { margin: 0 0 1rem; color: #4b5563; }
        label { display: block; font-weight: 600; margin: .8rem 0 .35rem; }
        input { width: 100%; padding: .75rem; border: 1px solid #d1d5db; border-radius: 8px; box-sizing: border-box; }
        button { width: 100%; margin-top: 1rem; padding: .85rem; border: 0; border-radius: 8px; background: #0f766e; color: #fff; font-weight: 700; cursor: pointer; }
        .alert { border-radius: 8px; padding: .75rem; margin: .75rem 0; font-size: .95rem; }
        .alert.error { background: #fee2e2; color: #991b1b; }
        .alert.success { background: #dcfce7; color: #166534; }
        .hint { margin-top: 1rem; font-size: .85rem; color: #6b7280; }
        code { background: #f3f4f6; padding: .1rem .25rem; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="card">
            <h1>Login</h1>
            <p><?= htmlspecialchars($appName ?? 'Dashboard PHP PBT', ENT_QUOTES, 'UTF-8') ?></p>

            <?php if (!empty($error)) : ?>
                <div class="alert error"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <?php if (!empty($success)) : ?>
                <div class="alert success"><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <form method="post" action="/login">
                <label for="email">E-mail</label>
                <input id="email" type="email" name="email" required value="<?= htmlspecialchars((string) ($oldEmail ?? ''), ENT_QUOTES, 'UTF-8') ?>">

                <label for="password">Senha</label>
                <input id="password" type="password" name="password" required>

                <button type="submit">Entrar</button>
            </form>

            <div class="hint">
                Seed admin inicial: <code>admin@igrejasocial.local</code> / <code>admin123</code>
            </div>
        </div>
    </div>
</body>
</html>

