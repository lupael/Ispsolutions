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
        'hash' => env('RADIUS_PASSWORD_HASH', 'cleartext'), // cleartext, md5, sha1
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
