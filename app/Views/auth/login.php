<?php
declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= htmlspecialchars($appName ?? 'Dashboard PHP PBT', ENT_QUOTES, 'UTF-8') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
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
                            <img src="/assets/logo_acao.jpg" alt="Logo acao" class="login-logo">
                        </div>
                        <div class="mb-4">
                            <div class="text-uppercase small text-secondary fw-semibold">Igreja Social</div>
                            <h1 class="h4 mb-1">Login</h1>
                            <p class="text-secondary mb-0"><?= htmlspecialchars($appName ?? 'Dashboard PHP PBT', ENT_QUOTES, 'UTF-8') ?></p>
                        </div>

                        <?php if (!empty($error)) : ?>
                            <div class="alert alert-danger border-0 shadow-sm"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div>
                        <?php endif; ?>

                        <?php if (!empty($success)) : ?>
                            <div class="alert alert-success border-0 shadow-sm"><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></div>
                        <?php endif; ?>

                        <form method="post" action="/login">
                            <div class="mb-3">
                                <label for="email" class="form-label">E-mail</label>
                                <input id="email" type="email" name="email" class="form-control form-control-lg" required value="<?= htmlspecialchars((string) ($oldEmail ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Senha</label>
                                <input id="password" type="password" name="password" class="form-control form-control-lg" required>
                            </div>

                            <button type="submit" class="btn btn-teal text-white w-100 btn-lg">Entrar</button>
                        </form>

                        <div class="mt-3 text-center">
                            <a href="/forgot-password" class="small">Esqueci minha senha</a>
                        </div>

                        <div class="mt-4 small text-secondary">
                            Seed admin inicial: <code>admin@igrejasocial.local</code> / <code>admin123</code>
                        </div>
                        <?php if (!empty($resetHint)) : ?>
                            <div class="mt-2 small">
                                <strong>Dev token:</strong> <code><?= htmlspecialchars((string) $resetHint, ENT_QUOTES, 'UTF-8') ?></code>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
