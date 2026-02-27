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
];
