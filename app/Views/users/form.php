<?php
declare(strict_types=1);

$isEdit = ($mode ?? 'create') === 'edit';
$userData = is_array($user ?? null) ? $user : [];
?>
<div class="row justify-content-center">
    <div class="col-12 col-xl-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h2 class="h4 mb-1"><?= $isEdit ? 'Editar usuario' : 'Novo usuario' ?></h2>
                <p class="text-secondary mb-4">Perfis: `admin`, `voluntario`, `pastoral`, `viewer`.</p>

                <?php if (!empty($error)) : ?>
                    <div class="alert alert-danger border-0 shadow-sm"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>

                <form method="post" action="<?= $isEdit ? '/users/update?id=' . (int) ($userData['id'] ?? 0) : '/users' ?>">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label for="name" class="form-label">Nome</label>
                            <input id="name" name="name" class="form-control" required value="<?= htmlspecialchars((string) ($userData['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="email" class="form-label">E-mail</label>
                            <input id="email" type="email" name="email" class="form-control" required value="<?= htmlspecialchars((string) ($userData['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="role" class="form-label">Perfil</label>
                            <select id="role" name="role" class="form-select" required>
                                <?php foreach (($roles ?? []) as $role) : ?>
                                    <option value="<?= htmlspecialchars((string) $role, ENT_QUOTES, 'UTF-8') ?>" <?= ((string) ($userData['role'] ?? '') === (string) $role) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars((string) $role, ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="password" class="form-label"><?= $isEdit ? 'Nova senha (opcional)' : 'Senha' ?></label>
                            <input id="password" type="password" name="password" class="form-control" <?= $isEdit ? '' : 'required' ?>>
                        </div>

                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" <?= ((int) ($userData['is_active'] ?? 0) === 1) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="is_active">Usuario ativo</label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2 mt-4">
                        <button type="submit" class="btn btn-teal text-white"><?= $isEdit ? 'Salvar alteracoes' : 'Criar usuario' ?></button>
                        <a class="btn btn-outline-secondary" href="/users">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
