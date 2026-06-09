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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    'zego' => [
        'video' => [
            'app_id' => env('ZEGO_APP_ID'),
            'server_secret' => env('ZEGO_SERVER_SECRET'),
        ],
        'chat' => [
            'app_id' => env('ZEGO_CHAT_APP_ID'),
            'server_secret' => env('ZEGO_CHAT_SERVER_SECRET'),
        ],
    ],

    'signature_form' => [
        'script_url' => env(
            'SIGNATURE_FORM_SCRIPT_URL',
            'https://script.google.com/macros/s/AKfycbxjD7WfIAb0l92JVE148D-8HYmjSv1CQG9tWQogHDQG8AtyJkV5umevsoz7_H2-iVCm/exec'
        ),
    ],

];
