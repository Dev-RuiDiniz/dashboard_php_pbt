<?php
declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir senha - <?= htmlspecialchars($appName ?? 'Dashboard PHP PBT', ENT_QUOTES, 'UTF-8') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/app.css">
</head>
<body class="login-page">
    <div class="container min-vh-100 d-flex align-items-center py-4">
        <div class="row justify-content-center w-100">
            <div class="col-12 col-sm-10 col-md-8 col-lg-5 col-xl-4">
                <div class="card border-0 shadow-lg rounded-4 login-card">
                    <div class="card-body p-4 p-md-5">
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <img src="/assets/logo_cliente.jpeg" alt="Logo cliente" class="login-logo">
                            <img src="/assets/plano_fundo.jpg" alt="Plano de fundo" class="login-logo">
                        </div>
                        <div class="text-uppercase small text-secondary fw-semibold">Igreja Social</div>
                        <h1 class="h4 mb-1">Redefinir senha</h1>
                        <p class="text-secondary mb-4">Digite a nova senha.</p>

                        <?php if (!empty($error)) : ?>
                            <div class="alert alert-danger"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div>
                        <?php endif; ?>

                        <form method="post" action="/reset-password">
                            <input type="hidden" name="token" value="<?= htmlspecialchars((string) ($token ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                            <div class="mb-3">
                                <label for="password" class="form-label">Nova senha</label>
                                <input id="password" type="password" name="password" class="form-control form-control-lg" minlength="8" required>
                            </div>
                            <div class="mb-3">
                                <label for="password_confirm" class="form-label">Confirmar nova senha</label>
                                <input id="password_confirm" type="password" name="password_confirm" class="form-control form-control-lg" minlength="8" required>
                            </div>
                            <button type="submit" class="btn btn-teal text-white w-100 btn-lg">Salvar nova senha</button>
                        </form>

                        <div class="mt-3 text-center">
                            <a href="/login" class="small">Voltar ao login</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

