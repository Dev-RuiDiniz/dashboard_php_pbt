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
        @import url("https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Source+Sans+3:wght@400;500;600&display=swap");
        body {
            font-family: "Source Sans 3", "Segoe UI", Tahoma, sans-serif;
            margin: 2rem;
            background: linear-gradient(160deg, #fffefb 0%, #f3ecde 100%);
            color: #2b2318;
        }
        .card {
            background: linear-gradient(180deg, #fffefb 0%, #f9f2e5 100%);
            border-radius: 16px;
            border: 1px solid #e5dbc8;
            padding: 1.5rem;
            max-width: 720px;
            box-shadow: 0 16px 30px rgba(94, 73, 40, 0.12);
        }
        h1 {
            font-family: "Cormorant Garamond", Georgia, "Times New Roman", serif;
            font-size: 2.2rem;
            margin-top: 0;
        }
        code {
            background: #f2e9d8;
            color: #674a22;
            padding: .1rem .35rem;
            border-radius: 6px;
            border: 1px solid #e2d2b3;
        }
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

