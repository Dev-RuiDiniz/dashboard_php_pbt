# Sprint 19 - Auditoria, seguranca e hardening

## Entregas
- `audit_logs` ampliado para eventos relevantes:
  - autenticacao (sucesso/falha/bloqueio/logout)
  - recuperacao de senha (solicitacao/conclusao)
  - usuarios (create/update/toggle)
  - visitas (create/update/conclude/delete)
  - relatorios (view/export PDF)
- Bloqueio por tentativas de login:
  - contador de falhas por usuario
  - bloqueio temporario (`locked_until`)
- Recuperacao de senha com token:
  - solicitacao de token
  - validacao de expiracao
  - redefinicao da senha
- Revisao de permissoes por modulo:
  - consolidacao de acessos com `reports.view`, `visits.*`, `equipment.*`
- Revisao de sessao/cookies/headers:
  - `session.use_strict_mode`, cookie `HttpOnly`, `Secure`, `SameSite=Strict`
  - headers de hardening: `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, `Permissions-Policy`, `CSP`

## Banco de dados
- Novas colunas em `users`:
  - `failed_login_attempts`
  - `locked_until`
  - `password_reset_token_hash`
  - `password_reset_expires_at`
  - `password_reset_requested_at`
- `audit_logs.user_id` alterado para `NULL` com `ON DELETE SET NULL`
- Migration:
  - `database/migrations/003_security_hardening.sql`

## Rotas de autenticacao
- `GET /forgot-password`
- `POST /forgot-password`
- `GET /reset-password?token=...`
- `POST /reset-password`

