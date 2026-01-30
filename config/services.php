<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'mikrotik' => [
        'timeout' => env('MIKROTIK_API_TIMEOUT', 60),
        'default_port' => env('MIKROTIK_DEFAULT_PORT', 8728),
        'max_retries' => env('MIKROTIK_MAX_RETRIES', 3),
        'retry_delay' => env('MIKROTIK_RETRY_DELAY', 1000), // milliseconds
        'allow_private_ips' => env('MIKROTIK_ALLOW_PRIVATE_IPS', true), // Allow RFC1918 IPs for internal routers
    ],

    'whatsapp' => [
        'enabled' => env('WHATSAPP_ENABLED', false),
        'api_url' => env('WHATSAPP_API_URL', 'https://graph.facebook.com/v18.0'),
        'access_token' => env('WHATSAPP_ACCESS_TOKEN'),
        'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
        'app_secret' => env('WHATSAPP_APP_SECRET'),
        'default_country_code' => env('WHATSAPP_DEFAULT_COUNTRY_CODE', '880'),
    ],

    'telegram' => [
        'enabled' => env('TELEGRAM_ENABLED', false),
        'bot_token' => env('TELEGRAM_BOT_TOKEN'),
        'webhook_url' => env('TELEGRAM_WEBHOOK_URL'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Gateway Services
    |--------------------------------------------------------------------------
    |
    | Configuration for payment gateways including webhook signature verification
    | credentials. These are used to securely process payment webhooks.
    |
    */

    'bkash' => [
        'enabled' => env('BKASH_ENABLED', false),
        'app_key' => env('BKASH_APP_KEY'),
        'app_secret' => env('BKASH_APP_SECRET'),
        'username' => env('BKASH_USERNAME'),
        'password' => env('BKASH_PASSWORD'),
        'webhook_secret' => env('BKASH_WEBHOOK_SECRET'),
        'base_url' => env('BKASH_BASE_URL', 'https://checkout.pay.bka.sh/v1.2.0-beta'),
        'sandbox' => env('BKASH_SANDBOX', true),
    ],

    'nagad' => [
        'enabled' => env('NAGAD_ENABLED', false),
        'merchant_id' => env('NAGAD_MERCHANT_ID'),
        'merchant_number' => env('NAGAD_MERCHANT_NUMBER'),
        'public_key' => env('NAGAD_PUBLIC_KEY'),
        'private_key' => env('NAGAD_PRIVATE_KEY'),
        'callback_url' => env('NAGAD_CALLBACK_URL'),
        'base_url' => env('NAGAD_BASE_URL', 'http://sandbox.mynagad.com:10080/remote-payment-gateway-1.0/api/dfs'),
        'sandbox' => env('NAGAD_SANDBOX', true),
    ],

    'rocket' => [
        'enabled' => env('ROCKET_ENABLED', false),
        'merchant_id' => env('ROCKET_MERCHANT_ID'),
        'merchant_key' => env('ROCKET_MERCHANT_KEY'),
        'webhook_secret' => env('ROCKET_WEBHOOK_SECRET'),
        'base_url' => env('ROCKET_BASE_URL', 'https://rocket.com.bd/api'),
        'sandbox' => env('ROCKET_SANDBOX', true),
    ],

    'sslcommerz' => [
        'enabled' => env('SSLCOMMERZ_ENABLED', false),
        'store_id' => env('SSLCOMMERZ_STORE_ID'),
        'store_password' => env('SSLCOMMERZ_STORE_PASSWORD'),
        'base_url' => env('SSLCOMMERZ_BASE_URL', 'https://sandbox.sslcommerz.com'),
        'sandbox' => env('SSLCOMMERZ_SANDBOX', true),
    ],

];
