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
