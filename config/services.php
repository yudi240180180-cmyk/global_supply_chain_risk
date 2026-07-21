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
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
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

    'restcountries' => [
        'key' => env('RESTCOUNTRIES_API_KEY', 'rc_live_ed1212343f104f3fa9fc99edcda4af15'),
        'url' => env('RESTCOUNTRIES_URL', 'https://api.restcountries.com/countries/v5'),
    ],

    'worldbank' => [
        'url' => env('WORLDBANK_URL', 'https://api.worldbank.org/v2'),
    ],

    'openmeteo' => [
        'url' => env('OPENMETEO_URL', 'https://api.open-meteo.com/v1/forecast'),
    ],

    'exchangerate' => [
        'key' => env('EXCHANGERATE_API_KEY', '19e147ef22ce4522f33e4916'),
        'url' => env('EXCHANGERATE_URL', 'https://v6.exchangerate-api.com/v6'),
    ],

    'gnews' => [
        'key' => env('GNEWS_API_KEY', '1db36192ec7c27c05c46ef0cad96f1a0'),
        'url' => env('GNEWS_URL', 'https://gnews.io/api/v4'),
    ],

];