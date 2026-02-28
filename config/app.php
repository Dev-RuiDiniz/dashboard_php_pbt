<?php

declare(strict_types=1);

return [
    'name' => $_ENV['APP_NAME'] ?? 'Dashboard PHP PBT',
    'env' => $_ENV['APP_ENV'] ?? 'production',
    'debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOL),
    'url' => $_ENV['APP_URL'] ?? 'http://localhost',
    'auth' => [
        'max_login_attempts' => max(1, (int) ($_ENV['AUTH_MAX_LOGIN_ATTEMPTS'] ?? 5)),
        'lock_minutes' => max(1, (int) ($_ENV['AUTH_LOCK_MINUTES'] ?? 15)),
        'reset_token_ttl_minutes' => max(5, (int) ($_ENV['AUTH_RESET_TOKEN_TTL_MINUTES'] ?? 60)),
    ],
    'alerts' => [
        'stale_days' => max(1, (int) ($_ENV['ALERT_STALE_DAYS'] ?? 30)),
    ],
    'cep_lookup' => [
        'correios_base_url' => trim((string) ($_ENV['CEP_CORREIOS_BASE_URL'] ?? 'https://api.correios.com.br/cep/v2')),
        'correios_bearer_token' => trim((string) ($_ENV['CEP_CORREIOS_BEARER_TOKEN'] ?? '')),
        'enable_viacep_fallback' => filter_var($_ENV['CEP_ENABLE_VIACEP_FALLBACK'] ?? true, FILTER_VALIDATE_BOOL),
        'timeout_seconds' => max(2, (int) ($_ENV['CEP_LOOKUP_TIMEOUT'] ?? 6)),
    ],
];
