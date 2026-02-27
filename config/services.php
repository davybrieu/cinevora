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

    'tmdb' => [
        'api_key' => env('TMDB_API_KEY'),
        'base_url' => 'https://api.themoviedb.org/3',
        'image_base_url' => 'https://image.tmdb.org/t/p/',
    ],

    'opensubtitles' => [
        'api_key' => env('OPENSUBTITLES_API_KEY'),
        'api_url' => env('OPENSUBTITLES_API_URL', 'https://api.opensubtitles.com/api/v1'),
        'user_agent' => env('OPENSUBTITLES_USER_AGENT', env('APP_NAME', 'Cinevora')),
        'bearer_token' => env('OPENSUBTITLES_BEARER_TOKEN'),
    ],

    'theintrodb' => [
        'api_key' => env('THEINTRODB_API_KEY'),
        'api_url' => env('THEINTRODB_API_URL', 'https://api.theintrodb.com'),
    ],
];
