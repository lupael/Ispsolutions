<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Rate Limiting Configuration
    |--------------------------------------------------------------------------
    |
    | Configure rate limits for different parts of the application.
    | Limits are specified as requests per minute.
    |
    */

    'api' => [
        'enabled' => env('RATE_LIMIT_API_ENABLED', true),
        'max_attempts' => env('RATE_LIMIT_API_MAX_ATTEMPTS', 60),
        'decay_minutes' => env('RATE_LIMIT_API_DECAY_MINUTES', 1),
    ],

    'login' => [
        'enabled' => env('RATE_LIMIT_LOGIN_ENABLED', true),
        'max_attempts' => env('RATE_LIMIT_LOGIN_MAX_ATTEMPTS', 5),
        'decay_minutes' => env('RATE_LIMIT_LOGIN_DECAY_MINUTES', 1),
    ],

    'webhooks' => [
        'enabled' => env('RATE_LIMIT_WEBHOOKS_ENABLED', true),
        'max_attempts' => env('RATE_LIMIT_WEBHOOKS_MAX_ATTEMPTS', 100),
        'decay_minutes' => env('RATE_LIMIT_WEBHOOKS_DECAY_MINUTES', 1),
    ],

    'public_api' => [
        'enabled' => env('RATE_LIMIT_PUBLIC_API_ENABLED', true),
        'max_attempts' => env('RATE_LIMIT_PUBLIC_API_MAX_ATTEMPTS', 30),
        'decay_minutes' => env('RATE_LIMIT_PUBLIC_API_DECAY_MINUTES', 1),
    ],

    'global' => [
        'enabled' => env('RATE_LIMIT_GLOBAL_ENABLED', true),
        'max_attempts' => env('RATE_LIMIT_GLOBAL_MAX_ATTEMPTS', 100),
        'decay_minutes' => env('RATE_LIMIT_GLOBAL_DECAY_MINUTES', 1),
    ],
];
