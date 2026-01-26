<?php

return [
    /*
    |--------------------------------------------------------------------------
    | RADIUS Database Connection
    |--------------------------------------------------------------------------
    |
    | The database connection name for RADIUS tables.
    |
    */
    'connection' => env('RADIUS_DB_CONNECTION', 'radius'),

    /*
    |--------------------------------------------------------------------------
    | RADIUS Server Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for RADIUS server connectivity.
    |
    */
    'server_ip' => env('RADIUS_SERVER_IP', '127.0.0.1'),
    'authentication_port' => env('RADIUS_AUTH_PORT', 1812),
    'accounting_port' => env('RADIUS_ACCT_PORT', 1813),
    'interim_update' => env('RADIUS_INTERIM_UPDATE', '5m'),
    'primary_authenticator' => env('RADIUS_PRIMARY_AUTH', 'hybrid'),

    /*
    |--------------------------------------------------------------------------
    | Authentication Settings
    |--------------------------------------------------------------------------
    |
    | Settings for RADIUS authentication.
    |
    */
    'authenticate' => [
        // WARNING: The default password hash is 'md5' for better security than cleartext.
        // Options: 'md5', 'sha1', 'cleartext' (not recommended for production)
        // For production environments, use 'md5' or 'sha1' for better security.
        'hash' => env('RADIUS_PASSWORD_HASH', 'md5'), // md5 (default), sha1, cleartext
    ],

    /*
    |--------------------------------------------------------------------------
    | Accounting Settings
    |--------------------------------------------------------------------------
    |
    | Settings for RADIUS accounting.
    |
    */
    'accounting' => [
        'session_timeout' => env('RADIUS_SESSION_TIMEOUT', 86400), // 24 hours
    ],

    /*
    |--------------------------------------------------------------------------
    | Netwatch Failover Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for automatic failover using MikroTik Netwatch.
    |
    */
    'netwatch' => [
        'enabled' => env('RADIUS_NETWATCH_ENABLED', true),
        'interval' => env('RADIUS_NETWATCH_INTERVAL', '1m'),
        'timeout' => env('RADIUS_NETWATCH_TIMEOUT', '1s'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Reply Attributes
    |--------------------------------------------------------------------------
    |
    | Default RADIUS reply attributes for new users.
    |
    */
    'default_reply_attributes' => [
        'Framed-Protocol' => 'PPP',
        'Service-Type' => 'Framed-User',
    ],
];
