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
            'resetHint' => (string) Session::consumeFlash('reset_hint', ''),
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
            $auth = $this->authService();
            $result = $auth->attempt($email, $password);
            if (!(bool) ($result['ok'] ?? false)) {
                $reason = (string) ($result['reason'] ?? 'invalid_credentials');
                if ($reason === 'locked') {
                    $lockedUntil = (string) ($result['locked_until'] ?? '');
                    Session::flash('error', 'Usuario temporariamente bloqueado por tentativas invalidas. Tente novamente apos ' . $lockedUntil . '.');
                } elseif ($reason === 'inactive') {
                    Session::flash('error', 'Usuario inativo. Procure um administrador.');
                } else {
                    Session::flash('error', 'Credenciais invalidas.');
                }
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
            $this->authService()->logout();
        } catch (Throwable $exception) {
            Session::remove('auth_user');
            session_regenerate_id(true);
        }

        Session::flash('success', 'Logout realizado com sucesso.');
        Response::redirect('/login');
    }

    public function showForgotPassword(): void
    {
        View::render('auth.forgot_password', [
            'appName' => (string) ($this->container->get('config')['app']['name'] ?? 'Dashboard PHP PBT'),
            'error' => Session::consumeFlash('error'),
            'success' => Session::consumeFlash('success'),
            'oldEmail' => (string) Session::consumeFlash('old_email', ''),
        ]);
    }

    public function requestPasswordReset(): void
    {
        $email = trim((string) ($_POST['email'] ?? ''));
        Session::flash('old_email', $email);
        if ($email === '') {
            Session::flash('error', 'Informe o e-mail.');
            Response::redirect('/forgot-password');
        }

        try {
            $token = $this->authService()->requestPasswordReset($email);
            if ($token !== null) {
                Session::flash('reset_hint', 'Token de recuperacao (ambiente local): ' . $token);
            }
        } catch (Throwable $exception) {
            Session::flash('error', 'Falha ao solicitar recuperacao de senha.');
            Response::redirect('/forgot-password');
        }

        Session::flash('success', 'Se o e-mail existir, um token de recuperacao foi gerado.');
        Response::redirect('/login');
    }

    public function showResetPassword(): void
    {
        $token = trim((string) ($_GET['token'] ?? ''));
        if ($token === '') {
            Session::flash('error', 'Token de recuperacao invalido.');
            Response::redirect('/forgot-password');
        }

        try {
            $valid = $this->authService()->validateResetToken($token) !== null;
        } catch (Throwable $exception) {
            $valid = false;
        }
        if (!$valid) {
            Session::flash('error', 'Token invalido ou expirado.');
            Response::redirect('/forgot-password');
        }

        View::render('auth.reset_password', [
            'appName' => (string) ($this->container->get('config')['app']['name'] ?? 'Dashboard PHP PBT'),
            'error' => Session::consumeFlash('error'),
            'token' => $token,
        ]);
    }

    public function resetPassword(): void
    {
        $token = trim((string) ($_POST['token'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $passwordConfirm = (string) ($_POST['password_confirm'] ?? '');

        if ($token === '') {
            Session::flash('error', 'Token de recuperacao invalido.');
            Response::redirect('/forgot-password');
        }
        if (strlen($password) < 8) {
            Session::flash('error', 'A nova senha deve ter no minimo 8 caracteres.');
            Response::redirect('/reset-password?token=' . urlencode($token));
        }
        if ($password !== $passwordConfirm) {
            Session::flash('error', 'A confirmacao de senha nao confere.');
            Response::redirect('/reset-password?token=' . urlencode($token));
        }

        try {
            $ok = $this->authService()->resetPassword($token, $password);
        } catch (Throwable $exception) {
            $ok = false;
        }
        if (!$ok) {
            Session::flash('error', 'Token invalido ou expirado.');
            Response::redirect('/forgot-password');
        }

        Session::flash('success', 'Senha redefinida com sucesso. Faca login novamente.');
        Response::redirect('/login');
    }

    private function authService(): AuthService
    {
        /** @var PDO $pdo */
        $pdo = $this->container->get('db');
        $config = $this->container->get('config');
        $authConfig = $config['app']['auth'] ?? [];

        return new AuthService(
            $pdo,
            (int) ($authConfig['max_login_attempts'] ?? 5),
            (int) ($authConfig['lock_minutes'] ?? 15),
            (int) ($authConfig['reset_token_ttl_minutes'] ?? 60)
        );
    }
}
