<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Session;
use App\Models\UserModel;
use PDO;

final class AuthService
{
    public function __construct(
        private readonly PDO $pdo,
        private readonly int $maxAttempts = 5,
        private readonly int $lockMinutes = 15,
        private readonly int $resetTokenTtlMinutes = 60
    )
    {
    }

    public function attempt(string $email, string $password): array
    {
        $users = new UserModel($this->pdo);
        $audit = new AuditService($this->pdo);
        $user = $users->findAuthByEmail($email);
        if ($user === null) {
            $audit->log('auth.login_failed_unknown_email', 'users', null, null, [
                'email' => $email,
            ]);
            return ['ok' => false, 'reason' => 'invalid_credentials'];
        }

        $userId = (int) ($user['id'] ?? 0);
        $lockedUntil = (string) ($user['locked_until'] ?? '');
        if ($lockedUntil !== '' && strtotime($lockedUntil) > time()) {
            $audit->log('auth.login_blocked_locked', 'users', $userId, $userId, [
                'email' => $email,
                'locked_until' => $lockedUntil,
            ]);
            return ['ok' => false, 'reason' => 'locked', 'locked_until' => $lockedUntil];
        }

        if ((int) ($user['is_active'] ?? 0) !== 1) {
            $audit->log('auth.login_failed_inactive', 'users', $userId, $userId, [
                'email' => $email,
            ]);
            return ['ok' => false, 'reason' => 'inactive'];
        }

        $hash = (string) ($user['password_hash'] ?? '');
        if (!password_verify($password, $hash)) {
            $failure = $users->markFailedLogin($userId, $this->maxAttempts, $this->lockMinutes);
            $audit->log('auth.login_failed_invalid_password', 'users', $userId, $userId, [
                'email' => $email,
                'attempts' => (int) ($failure['attempts'] ?? 0),
                'locked_until' => $failure['locked_until'] ?? null,
            ]);
            if (!empty($failure['locked_until'])) {
                return ['ok' => false, 'reason' => 'locked', 'locked_until' => (string) $failure['locked_until']];
            }
            return ['ok' => false, 'reason' => 'invalid_credentials'];
        }

        $users->clearLoginFailures($userId);
        session_regenerate_id(true);

        Session::set('auth_user', [
            'id' => $userId,
            'name' => (string) $user['name'],
            'email' => (string) $user['email'],
            'role' => (string) $user['role'],
        ]);

        $audit->log('auth.login_success', 'users', $userId, $userId, [
            'email' => $email,
        ]);

        return ['ok' => true, 'reason' => 'success'];
    }

    public function check(): bool
    {
        return Session::has('auth_user');
    }

    public function user(): ?array
    {
        $user = Session::get('auth_user');
        return is_array($user) ? $user : null;
    }

    public function logout(): void
    {
        $user = $this->user();
        $userId = is_array($user) ? (int) ($user['id'] ?? 0) : null;
        (new AuditService($this->pdo))->log('auth.logout', 'users', $userId, $userId, []);
        Session::remove('auth_user');
        session_regenerate_id(true);
    }

    public function requestPasswordReset(string $email): ?string
    {
        $users = new UserModel($this->pdo);
        $audit = new AuditService($this->pdo);
        $user = $users->findByEmail($email);
        if ($user === null) {
            $audit->log('auth.password_reset_request_unknown_email', 'users', null, null, [
                'email' => $email,
            ]);
            return null;
        }

        $userId = (int) ($user['id'] ?? 0);
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+' . $this->resetTokenTtlMinutes . ' minutes'));

        $users->setPasswordResetToken($userId, $tokenHash, $expiresAt);
        $audit->log('auth.password_reset_requested', 'users', $userId, $userId, [
            'email' => $email,
            'expires_at' => $expiresAt,
        ]);

        return $token;
    }

    public function validateResetToken(string $token): ?array
    {
        $users = new UserModel($this->pdo);
        $user = $users->findByPasswordResetToken($token);
        if ($user === null) {
            return null;
        }

        $expiresAt = (string) ($user['password_reset_expires_at'] ?? '');
        if ($expiresAt === '' || strtotime($expiresAt) < time()) {
            return null;
        }
        if ((int) ($user['is_active'] ?? 0) !== 1) {
            return null;
        }
        return $user;
    }

    public function resetPassword(string $token, string $newPassword): bool
    {
        $users = new UserModel($this->pdo);
        $audit = new AuditService($this->pdo);
        $user = $this->validateResetToken($token);
        if ($user === null) {
            return false;
        }

        $userId = (int) ($user['id'] ?? 0);
        $users->updatePasswordById($userId, password_hash($newPassword, PASSWORD_DEFAULT));
        $users->clearPasswordResetToken($userId);
        $users->clearLoginFailures($userId);

        $audit->log('auth.password_reset_completed', 'users', $userId, $userId, []);
        return true;
    }
}
