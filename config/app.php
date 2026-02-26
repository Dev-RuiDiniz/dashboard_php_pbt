<?php

declare(strict_types=1);

return [
    'name' => $_ENV['APP_NAME'] ?? 'Dashboard PHP PBT',
    'env' => $_ENV['APP_ENV'] ?? 'production',
    'debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOL),
    'url' => $_ENV['APP_URL'] ?? 'http://localhost',
];

