<?php

// config for HusamTariq/FilamentSmsSender
return [
    /*
    |--------------------------------------------------------------------------
    | SMS Gateway Settings
    |--------------------------------------------------------------------------
    |
    | Default settings for the SMS gateway configuration. These can be
    | overridden through the admin panel settings page.
    |
    */
    'gateway' => [
        'method' => 'POST',
        'endpoint' => '',
        'parameters' => [],
        'headers' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | OTP Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for One-Time Password functionality.
    |
    */
    'otp' => [
        'length' => 6,
        'expiry_minutes' => 10,
        'template' => 'Your OTP code is: {{ otp_code }}',
        'rate_limit' => [
            'enabled' => true,
            'max_attempts' => 3,
            'window_minutes' => 5,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Tables
    |--------------------------------------------------------------------------
    |
    | Customize the table names used by the plugin.
    |
    */
    'tables' => [
        'otp_codes' => 'sms_sender_otp_codes',
        'providers' => 'sms_providers',
    ],
];
