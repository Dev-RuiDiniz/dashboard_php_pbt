<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Container;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Services\AuthService;
use PDO;
use Throwable;

final class AuthController
{
    public function __construct(private readonly Container $container)
    {
    }

    public function showLogin(): void
    {
        if (Session::has('auth_user')) {
            Response::redirect('/dashboard');
        }

        View::render('auth.login', [
            'appName' => (string) ($this->container->get('config')['app']['name'] ?? 'Dashboard PHP PBT'),
            'error' => Session::consumeFlash('error'),
            'success' => Session::consumeFlash('success'),
            'oldEmail' => (string) Session::consumeFlash('old_email', ''),
        ]);
    }

    public function login(): void
    {
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        Session::flash('old_email', $email);

        if ($email === '' || $password === '') {
            Session::flash('error', 'Informe e-mail e senha.');
            Response::redirect('/login');
        }

        try {
            /** @var PDO $pdo */
            $pdo = $this->container->get('db');
            $auth = new AuthService($pdo);

            if (!$auth->attempt($email, $password)) {
                Session::flash('error', 'Credenciais invalidas.');
                Response::redirect('/login');
            }
        } catch (Throwable $exception) {
            Session::flash('error', 'Falha ao autenticar. Verifique a conexao com o banco.');
            Response::redirect('/login');
        }

        Session::flash('success', 'Login realizado com sucesso.');
        Response::redirect('/dashboard');
    }

    public function logout(): void
    {
        try {
            /** @var PDO $pdo */
            $pdo = $this->container->get('db');
            (new AuthService($pdo))->logout();
        } catch (Throwable $exception) {
            Session::remove('auth_user');
            session_regenerate_id(true);
        }

        Session::flash('success', 'Logout realizado com sucesso.');
        Response::redirect('/login');
    }
}

