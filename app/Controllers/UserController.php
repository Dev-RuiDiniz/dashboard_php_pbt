<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Container;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\UserModel;
use PDO;
use Throwable;

final class UserController
{
    private const ROLES = ['admin', 'voluntario', 'pastoral', 'viewer'];

    public function __construct(private readonly Container $container)
    {
    }

    public function index(): void
    {
        try {
            $users = $this->userModel()->all();
        } catch (Throwable $exception) {
            Session::flash('error', 'Falha ao carregar usuarios. Verifique o banco.');
            $users = [];
        }

        View::render('users.index', [
            '_layout' => 'layouts.app',
            'appName' => (string) ($this->container->get('config')['app']['name'] ?? 'Dashboard PHP PBT'),
            'pageTitle' => 'Usuarios',
            'activeMenu' => 'users',
            'users' => $users,
            'authUser' => Session::get('auth_user', []),
            'success' => Session::consumeFlash('success'),
            'error' => Session::consumeFlash('error'),
        ]);
    }

    public function create(): void
    {
        $old = Session::consumeFlash('form_old');
        $user = [
            'name' => '',
            'email' => '',
            'role' => 'voluntario',
            'is_active' => 1,
        ];
        if (is_array($old)) {
            $user = array_merge($user, $old);
        }

        $this->renderForm([
            'mode' => 'create',
            'user' => $user,
        ]);
    }

    public function store(): void
    {
        $input = $this->sanitizeInput($_POST);
        $password = (string) ($_POST['password'] ?? '');

        if (!$this->validateRequired($input['name'], $input['email'])) {
            Session::flash('error', 'Nome e e-mail sao obrigatorios.');
            Session::flash('form_old', $input);
            Response::redirect('/users/create');
        }

        if ($password === '') {
            Session::flash('error', 'Senha e obrigatoria no cadastro.');
            Session::flash('form_old', $input);
            Response::redirect('/users/create');
        }

        if (!$this->validateRole($input['role'])) {
            Session::flash('error', 'Perfil invalido.');
            Session::flash('form_old', $input);
            Response::redirect('/users/create');
        }

        try {
            $userModel = $this->userModel();

            if ($userModel->findByEmailExcludingId($input['email']) !== null) {
                Session::flash('error', 'Ja existe usuario com este e-mail.');
                Session::flash('form_old', $input);
                Response::redirect('/users/create');
            }

            $userModel->create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password_hash' => password_hash($password, PASSWORD_BCRYPT),
                'role' => $input['role'],
                'is_active' => $input['is_active'],
            ]);
        } catch (Throwable $exception) {
            Session::flash('error', 'Falha ao criar usuario.');
            Session::flash('form_old', $input);
            Response::redirect('/users/create');
        }

        Session::flash('success', 'Usuario criado com sucesso.');
        Response::redirect('/users');
    }

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            Session::flash('error', 'Usuario invalido.');
            Response::redirect('/users');
        }

        try {
            $user = $this->userModel()->findById($id);
        } catch (Throwable $exception) {
            Session::flash('error', 'Falha ao carregar usuario.');
            Response::redirect('/users');
        }

        if ($user === null) {
            Session::flash('error', 'Usuario nao encontrado.');
            Response::redirect('/users');
        }

        $old = Session::consumeFlash('form_old');
        if (is_array($old)) {
            $user = array_merge($user, $old);
        }

        $this->renderForm([
            'mode' => 'edit',
            'user' => $user,
        ]);
    }

    public function update(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            Session::flash('error', 'Usuario invalido.');
            Response::redirect('/users');
        }

        $input = $this->sanitizeInput($_POST);
        $password = (string) ($_POST['password'] ?? '');

        if (!$this->validateRequired($input['name'], $input['email'])) {
            Session::flash('error', 'Nome e e-mail sao obrigatorios.');
            Session::flash('form_old', $input);
            Response::redirect('/users/edit?id=' . $id);
        }

        if (!$this->validateRole($input['role'])) {
            Session::flash('error', 'Perfil invalido.');
            Session::flash('form_old', $input);
            Response::redirect('/users/edit?id=' . $id);
        }

        try {
            $userModel = $this->userModel();
            $existing = $userModel->findById($id);

            if ($existing === null) {
                Session::flash('error', 'Usuario nao encontrado.');
                Response::redirect('/users');
            }

            if ($userModel->findByEmailExcludingId($input['email'], $id) !== null) {
                Session::flash('error', 'Ja existe usuario com este e-mail.');
                Session::flash('form_old', $input);
                Response::redirect('/users/edit?id=' . $id);
            }

            $authUser = Session::get('auth_user', []);
            $authUserId = is_array($authUser) ? (int) ($authUser['id'] ?? 0) : 0;

            if ($authUserId === $id && (int) $input['is_active'] !== 1) {
                Session::flash('error', 'Nao e permitido desativar o proprio usuario nesta tela.');
                Session::flash('form_old', $input);
                Response::redirect('/users/edit?id=' . $id);
            }

            $passwordHash = null;
            if ($password !== '') {
                $passwordHash = password_hash($password, PASSWORD_BCRYPT);
            }

            $userModel->update($id, [
                'name' => $input['name'],
                'email' => $input['email'],
                'role' => $input['role'],
                'is_active' => $input['is_active'],
                'password_hash' => $passwordHash,
            ]);
        } catch (Throwable $exception) {
            Session::flash('error', 'Falha ao atualizar usuario.');
            Session::flash('form_old', $input);
            Response::redirect('/users/edit?id=' . $id);
        }

        Session::flash('success', 'Usuario atualizado com sucesso.');
        Response::redirect('/users');
    }

    public function toggleActive(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            Session::flash('error', 'Usuario invalido.');
            Response::redirect('/users');
        }

        try {
            $userModel = $this->userModel();
            $user = $userModel->findById($id);

            if ($user === null) {
                Session::flash('error', 'Usuario nao encontrado.');
                Response::redirect('/users');
            }

            $authUser = Session::get('auth_user', []);
            $authUserId = is_array($authUser) ? (int) ($authUser['id'] ?? 0) : 0;
            if ($authUserId === $id) {
                Session::flash('error', 'Nao e permitido ativar/desativar o proprio usuario por este atalho.');
                Response::redirect('/users');
            }

            $newStatus = ((int) ($user['is_active'] ?? 0)) !== 1;
            $userModel->setActive($id, $newStatus);
        } catch (Throwable $exception) {
            Session::flash('error', 'Falha ao alterar status do usuario.');
            Response::redirect('/users');
        }

        Session::flash('success', 'Status do usuario atualizado com sucesso.');
        Response::redirect('/users');
    }

    private function renderForm(array $payload): void
    {
        View::render('users.form', [
            '_layout' => 'layouts.app',
            'appName' => (string) ($this->container->get('config')['app']['name'] ?? 'Dashboard PHP PBT'),
            'pageTitle' => ($payload['mode'] ?? 'create') === 'edit' ? 'Editar usuario' : 'Novo usuario',
            'activeMenu' => 'users',
            'authUser' => Session::get('auth_user', []),
            'mode' => $payload['mode'] ?? 'create',
            'user' => $payload['user'] ?? [],
            'roles' => self::ROLES,
            'error' => Session::consumeFlash('error'),
            'success' => Session::consumeFlash('success'),
        ]);
    }

    private function userModel(): UserModel
    {
        /** @var PDO $pdo */
        $pdo = $this->container->get('db');
        return new UserModel($pdo);
    }

    private function sanitizeInput(array $data): array
    {
        return [
            'name' => trim((string) ($data['name'] ?? '')),
            'email' => strtolower(trim((string) ($data['email'] ?? ''))),
            'role' => trim((string) ($data['role'] ?? '')),
            'is_active' => isset($data['is_active']) ? 1 : 0,
        ];
    }

    private function validateRole(string $role): bool
    {
        return in_array($role, self::ROLES, true);
    }

    private function validateRequired(string $name, string $email): bool
    {
        return $name !== '' && $email !== '';
    }
}
