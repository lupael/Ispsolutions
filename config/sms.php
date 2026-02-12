<?php

return [

    /*
    |--------------------------------------------------------------------------
    | SMS Gateway Configuration
    |--------------------------------------------------------------------------
    |
    | Configure your SMS gateway settings here. Multiple gateways are supported.
    |
    */

    'enabled' => env('SMS_ENABLED', false),

    'default_gateway' => env('SMS_DEFAULT_GATEWAY', 'twilio'),

    /*
    |--------------------------------------------------------------------------
    | Twilio Configuration
    |--------------------------------------------------------------------------
    */
    'twilio' => [
        'account_sid' => env('TWILIO_ACCOUNT_SID'),
        'auth_token' => env('TWILIO_AUTH_TOKEN'),
        'from_number' => env('TWILIO_FROM_NUMBER'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Nexmo/Vonage Configuration
    |--------------------------------------------------------------------------
    */
    'nexmo' => [
        'api_key' => env('NEXMO_API_KEY'),
        'api_secret' => env('NEXMO_API_SECRET'),
        'from_number' => env('NEXMO_FROM_NUMBER', 'ISP'),
    ],

    /*
    |--------------------------------------------------------------------------
    | BulkSMS Configuration
    |--------------------------------------------------------------------------
    */
    'bulksms' => [
        'username' => env('BULKSMS_USERNAME'),
        'password' => env('BULKSMS_PASSWORD'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Bangladeshi SMS Gateway Configuration
    |--------------------------------------------------------------------------
    |
    | Generic configuration for local Bangladeshi SMS gateways
    |
    */
    'bangladeshi' => [
        'api_key' => env('BD_SMS_API_KEY'),
        'sender_id' => env('BD_SMS_SENDER_ID'),
        'api_url' => env('BD_SMS_API_URL'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Maestro SMS Gateway Configuration
    |--------------------------------------------------------------------------
    */
    'maestro' => [
        'api_key' => env('MAESTRO_API_KEY'),
        'sender_id' => env('MAESTRO_SENDER_ID'),
        'api_url' => env('MAESTRO_API_URL', 'https://api.maestrosms.com/smsapi'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Robi SMS Gateway Configuration
    |--------------------------------------------------------------------------
    */
    'robi' => [
        'api_key' => env('ROBI_API_KEY'),
        'sender_id' => env('ROBI_SENDER_ID'),
    ],

    /*
    |--------------------------------------------------------------------------
    | M2M BD SMS Gateway Configuration
    |--------------------------------------------------------------------------
    */
    'm2mbd' => [
        'api_key' => env('M2MBD_API_KEY'),
        'sender_id' => env('M2MBD_SENDER_ID'),
    ],

    /*
    |--------------------------------------------------------------------------
    | BDBangladesh SMS Gateway Configuration
    |--------------------------------------------------------------------------
    */
    'bangladeshsms' => [
        'api_key' => env('BANGLADESHSMS_API_KEY'),
        'sender_id' => env('BANGLADESHSMS_SENDER_ID'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Bulk SMS BD Gateway Configuration
    |--------------------------------------------------------------------------
    */
    'bulksmsbd' => [
        'api_key' => env('BULKSMSBD_API_KEY'),
        'sender_id' => env('BULKSMSBD_SENDER_ID'),
    ],

    /*
    |--------------------------------------------------------------------------
    | BTS SMS Gateway Configuration
    |--------------------------------------------------------------------------
    */
    'btssms' => [
        'api_key' => env('BTSSMS_API_KEY'),
        'sender_id' => env('BTSSMS_SENDER_ID'),
    ],

    /*
    |--------------------------------------------------------------------------
    | 880 SMS Gateway Configuration
    |--------------------------------------------------------------------------
    */
    '880sms' => [
        'api_key' => env('880SMS_API_KEY'),
        'sender_id' => env('880SMS_SENDER_ID'),
    ],

    /*
    |--------------------------------------------------------------------------
    | BD Smart Pay SMS Gateway Configuration
    |--------------------------------------------------------------------------
    */
    'bdsmartpay' => [
        'api_key' => env('BDSMARTPAY_API_KEY'),
        'sender_id' => env('BDSMARTPAY_SENDER_ID'),
    ],

    /*
    |--------------------------------------------------------------------------
    | ElitBuzz SMS Gateway Configuration
    |--------------------------------------------------------------------------
    */
    'elitbuzz' => [
        'api_key' => env('ELITBUZZ_API_KEY'),
        'sender_id' => env('ELITBUZZ_SENDER_ID'),
    ],

    /*
    |--------------------------------------------------------------------------
    | SSL Wireless SMS Gateway Configuration
    |--------------------------------------------------------------------------
    */
    'sslwireless' => [
        'api_key' => env('SSLWIRELESS_API_KEY'),
        'sender_id' => env('SSLWIRELESS_SENDER_ID'),
    ],

    /*
    |--------------------------------------------------------------------------
    | ADN SMS Gateway Configuration
    |--------------------------------------------------------------------------
    */
    'adnsms' => [
        'api_key' => env('ADNSMS_API_KEY'),
        'sender_id' => env('ADNSMS_SENDER_ID'),
    ],

    /*
    |--------------------------------------------------------------------------
    | 24 SMS BD Gateway Configuration
    |--------------------------------------------------------------------------
    */
    '24smsbd' => [
        'api_key' => env('24SMSBD_API_KEY'),
        'sender_id' => env('24SMSBD_SENDER_ID'),
    ],

    /*
    |--------------------------------------------------------------------------
    | SMS Net Gateway Configuration
    |--------------------------------------------------------------------------
    */
    'smsnet' => [
        'api_key' => env('SMSNET_API_KEY'),
        'sender_id' => env('SMSNET_SENDER_ID'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Brand SMS Gateway Configuration
    |--------------------------------------------------------------------------
    */
    'brandsms' => [
        'api_key' => env('BRANDSMS_API_KEY'),
        'sender_id' => env('BRANDSMS_SENDER_ID'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Metrotel SMS Gateway Configuration
    |--------------------------------------------------------------------------
    */
    'metrotel' => [
        'api_key' => env('METROTEL_API_KEY'),
        'sender_id' => env('METROTEL_SENDER_ID'),
    ],

    /*
    |--------------------------------------------------------------------------
    | DianaHost SMS Gateway Configuration
    |--------------------------------------------------------------------------
    */
    'dianahost' => [
        'api_key' => env('DIANAHOST_API_KEY'),
        'sender_id' => env('DIANAHOST_SENDER_ID'),
    ],

    /*
    |--------------------------------------------------------------------------
    | SMS in BD Gateway Configuration
    |--------------------------------------------------------------------------
    */
    'smsinbd' => [
        'api_key' => env('SMSINBD_API_KEY'),
        'sender_id' => env('SMSINBD_SENDER_ID'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Dhakasoft BD SMS Gateway Configuration
    |--------------------------------------------------------------------------
    */
    'dhakasoftbd' => [
        'api_key' => env('DHAKASOFTBD_API_KEY'),
        'sender_id' => env('DHAKASOFTBD_SENDER_ID'),
    ],

];
