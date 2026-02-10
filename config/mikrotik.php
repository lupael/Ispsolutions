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
    // WARNING: The default MikroTik password is intentionally left as an empty string for development.
    // In production, you MUST set MIKROTIK_PASSWORD in your environment to a strong, non-empty value.
    'password' => env('MIKROTIK_PASSWORD', ''),

    /*
    |--------------------------------------------------------------------------
    | PPP Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for PPP/PPPoE configuration.
    |
    */
    'ppp_local_address' => env('MIKROTIK_PPP_LOCAL_ADDRESS', '10.0.0.1'),

    /*
    |--------------------------------------------------------------------------
    | Backup Settings
    |--------------------------------------------------------------------------
    |
    | Automatic backup configuration.
    |
    */
    'backup' => [
        'auto_backup_before_change' => env('MIKROTIK_AUTO_BACKUP', true),
        'retention_days' => env('MIKROTIK_BACKUP_RETENTION_DAYS', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Provisioning Settings
    |--------------------------------------------------------------------------
    |
    | Automatic provisioning configuration.
    |
    */
    'provisioning' => [
        'auto_provision_on_create' => env('MIKROTIK_AUTO_PROVISION', true),
        'update_on_password_change' => env('MIKROTIK_UPDATE_ON_PASSWORD_CHANGE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Connection Settings
    |--------------------------------------------------------------------------
    |
    | Timeout and retry settings for API connections.
    |
    */
    'timeout' => env('MIKROTIK_TIMEOUT', 30),
    'retry_attempts' => env('MIKROTIK_RETRY_ATTEMPTS', 3),
    'retry_delay' => env('MIKROTIK_RETRY_DELAY', 1), // seconds
    'auto_reconnect' => env('MIKROTIK_AUTO_RECONNECT', true),
];
