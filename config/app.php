<?php

declare(strict_types=1);

return [
    'name' => env('APP_NAME', 'Luxury Urban'),
    'url' => rtrim(env('APP_URL', 'http://localhost'), '/'),
    'debug' => filter_var(env('APP_DEBUG', false), FILTER_VALIDATE_BOOLEAN),
    'session_lifetime' => (int) env('SESSION_LIFETIME', 28800),
    'upload_path' => is_dir(dirname(__DIR__) . '/uploads/produtos')
        ? dirname(__DIR__) . '/uploads/produtos'
        : dirname(__DIR__) . '/public/uploads/produtos',
    'max_upload_size' => 2 * 1024 * 1024,
    'discount_limit_vendedor' => 10.0,
];
