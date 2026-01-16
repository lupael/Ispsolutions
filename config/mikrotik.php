<?php

return [
    /*
    |--------------------------------------------------------------------------
    | MikroTik Router Connection
    |--------------------------------------------------------------------------
    |
    | Connection settings for MikroTik RouterOS API.
    |
    */
    'host' => env('MIKROTIK_HOST', '192.168.88.1'),
    'port' => env('MIKROTIK_PORT', 8728),
    'username' => env('MIKROTIK_USERNAME', 'admin'),
    'password' => env('MIKROTIK_PASSWORD', ''),

    /*
    |--------------------------------------------------------------------------
    | Connection Settings
    |--------------------------------------------------------------------------
    |
    | Timeout and retry settings for API connections.
    |
    */
    'timeout' => env('MIKROTIK_TIMEOUT', 10),
    'retry_attempts' => env('MIKROTIK_RETRY_ATTEMPTS', 3),
    'retry_delay' => env('MIKROTIK_RETRY_DELAY', 1), // seconds
    'auto_reconnect' => env('MIKROTIK_AUTO_RECONNECT', true),
];
