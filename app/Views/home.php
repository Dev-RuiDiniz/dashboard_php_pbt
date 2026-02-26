<?php
declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($appName ?? 'Dashboard PHP PBT', ENT_QUOTES, 'UTF-8') ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 2rem; background: #f7f7f7; color: #222; }
        .card { background: #fff; border-radius: 10px; padding: 1.5rem; max-width: 720px; box-shadow: 0 2px 8px rgba(0,0,0,.08); }
        code { background: #eee; padding: .1rem .3rem; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="card">
        <h1><?= htmlspecialchars($appName ?? 'Dashboard PHP PBT', ENT_QUOTES, 'UTF-8') ?></h1>
        <p>Sprint 1 concluida: fundacao do projeto em PHP (MVC simples).</p>
        <p>Status da conexao PDO: <strong><?= htmlspecialchars($dbStatus ?? 'desconhecido', ENT_QUOTES, 'UTF-8') ?></strong></p>
        <p>Proximo passo: implementar autenticacao e estrutura de layout.</p>
        <p>Rota atual: <code>/</code></p>
    </div>
</body>
</html>

