<?php
declare(strict_types=1);

$layoutTitle = (string) ($pageTitle ?? 'Dashboard');
$layoutAppName = (string) ($appName ?? 'Dashboard PHP PBT');
$layoutActive = (string) ($activeMenu ?? 'dashboard');
$layoutUser = is_array($authUser ?? null) ? $authUser : [];
$layoutUserName = (string) ($layoutUser['name'] ?? 'Usuario');
$layoutUserRole = (string) ($layoutUser['role'] ?? '-');

$menu = [
    ['key' => 'dashboard', 'label' => 'Dashboard', 'href' => '/dashboard', 'icon' => 'grid'],
    ['key' => 'familias', 'label' => 'Familias', 'href' => '/families', 'icon' => 'people'],
    ['key' => 'fichas', 'label' => 'Fichas Sociais', 'href' => '/dashboard', 'icon' => 'clipboard2-heart'],
    ['key' => 'entregas', 'label' => 'Entregas', 'href' => '/dashboard', 'icon' => 'box-seam'],
    ['key' => 'equipamentos', 'label' => 'Equipamentos', 'href' => '/dashboard', 'icon' => 'wheelchair'],
    ['key' => 'visitas', 'label' => 'Visitas', 'href' => '/dashboard', 'icon' => 'house-heart'],
    ['key' => 'relatorios', 'label' => 'Relatorios', 'href' => '/dashboard', 'icon' => 'bar-chart'],
];

if ($layoutUserRole === 'admin') {
    $menu[] = ['key' => 'users', 'label' => 'Usuarios', 'href' => '/users', 'icon' => 'person-gear'];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($layoutTitle . ' - ' . $layoutAppName, ENT_QUOTES, 'UTF-8') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="/assets/app.css">
</head>
<body class="app-shell">
    <div class="container-fluid">
        <div class="row min-vh-100">
            <aside class="col-12 col-lg-3 col-xl-2 p-0 app-sidebar">
                <div class="d-flex flex-column h-100">
                    <div class="px-3 py-3 border-bottom border-light-subtle">
                        <div class="small text-uppercase text-secondary fw-semibold">Sistema</div>
                        <div class="fw-bold fs-5 text-white"><?= htmlspecialchars($layoutAppName, ENT_QUOTES, 'UTF-8') ?></div>
                    </div>

                    <nav class="nav nav-pills flex-column gap-1 px-2 py-3">
                        <?php foreach ($menu as $item) : ?>
                            <?php $active = $layoutActive === $item['key']; ?>
                            <a class="nav-link d-flex align-items-center gap-2 <?= $active ? 'active' : '' ?>" href="<?= htmlspecialchars((string) $item['href'], ENT_QUOTES, 'UTF-8') ?>">
                                <span class="icon-dot"></span>
                                <span><?= htmlspecialchars((string) $item['label'], ENT_QUOTES, 'UTF-8') ?></span>
                            </a>
                        <?php endforeach; ?>
                    </nav>

                    <div class="mt-auto p-3 border-top border-light-subtle text-white-50 small">
                        Sprint 4: layout base + dashboard inicial
                    </div>
                </div>
            </aside>

            <div class="col-12 col-lg-9 col-xl-10 p-0 d-flex flex-column">
                <header class="app-header border-bottom">
                    <div class="container-fluid px-3 px-md-4 py-3">
                        <div class="d-flex flex-column flex-md-row gap-2 align-items-md-center justify-content-between">
                            <div>
                                <h1 class="h4 mb-0"><?= htmlspecialchars($layoutTitle, ENT_QUOTES, 'UTF-8') ?></h1>
                                <div class="text-secondary small">
                                    <?= htmlspecialchars($layoutUserName, ENT_QUOTES, 'UTF-8') ?> ·
                                    <span class="text-uppercase"><?= htmlspecialchars($layoutUserRole, ENT_QUOTES, 'UTF-8') ?></span>
                                </div>
                            </div>
                            <form method="post" action="/logout" class="m-0">
                                <button type="submit" class="btn btn-outline-danger btn-sm">Sair</button>
                            </form>
                        </div>
                    </div>
                </header>

                <main class="flex-grow-1">
                    <div class="container-fluid px-3 px-md-4 py-4">
                        <?= $content ?>
                    </div>
                </main>

                <footer class="app-footer border-top bg-white">
                    <div class="container-fluid px-3 px-md-4 py-2 small text-secondary d-flex justify-content-between flex-wrap gap-2">
                        <span>Dashboard PHP PBT</span>
                        <span>Layout responsivo com Bootstrap 5</span>
                    </div>
                </footer>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
