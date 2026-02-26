<?php
declare(strict_types=1);

$auth = is_array($authUser ?? null) ? $authUser : [];
?>
<?php if (!empty($success)) : ?>
    <div class="alert alert-success shadow-sm border-0"><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>
<?php if (!empty($error)) : ?>
    <div class="alert alert-danger shadow-sm border-0"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="d-flex flex-column flex-md-row gap-2 justify-content-between align-items-md-center mb-3">
            <div>
                <h2 class="h5 mb-1">Usuarios do sistema</h2>
                <p class="text-secondary mb-0">CRUD admin com RBAC por rota/acao.</p>
            </div>
            <div class="d-flex gap-2">
                <a class="btn btn-teal text-white" href="/users/create">Novo usuario</a>
                <a class="btn btn-outline-secondary" href="/dashboard">Dashboard</a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle">
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
                        <td colspan="6" class="text-secondary">Nenhum usuario encontrado ou falha de conexao.</td>
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
                            <td><span class="badge text-bg-light border"><?= htmlspecialchars((string) ($row['role'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span></td>
                            <td>
                                <span class="badge <?= $isActive ? 'text-bg-success' : 'text-bg-danger' ?>">
                                    <?= $isActive ? 'ativo' : 'inativo' ?>
                                </span>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-2">
                                    <a class="btn btn-sm btn-outline-secondary" href="/users/edit?id=<?= $id ?>">Editar</a>
                                    <?php if (!$isSelf) : ?>
                                        <form method="post" action="/users/toggle?id=<?= $id ?>" class="m-0">
                                            <button type="submit" class="btn btn-sm <?= $isActive ? 'btn-outline-warning' : 'btn-outline-success' ?>">
                                                <?= $isActive ? 'Desativar' : 'Ativar' ?>
                                            </button>
                                        </form>
                                    <?php else : ?>
                                        <span class="small text-secondary align-self-center">(voce)</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
