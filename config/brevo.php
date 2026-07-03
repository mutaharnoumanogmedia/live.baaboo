<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Brevo API Key
    |--------------------------------------------------------------------------
    |
    | The API key used to authenticate against the Brevo (Sendinblue) API.
    | Generate one at https://app.brevo.com/settings/keys/api
    |
    */

    'api_key' => env('BREVO_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Sender Presets
    |--------------------------------------------------------------------------
    |
    | Named "from" identities you can pick from when sending an email through
    | BrevoService. Reference a preset by its key (e.g. 'winners') or pass an
    | explicit sender array/email at send time to override these.
    |
    | Each preset accepts an 'email' + optional 'name', OR an 'id' referencing
    | a sender configured in your Brevo account (useful for dedicated IPs).
    |
    */

    'default_sender' => env('BREVO_DEFAULT_SENDER', 'default'),

    'senders' => [
        'default' => [
            'email' => env('MAIL_FROM_ADDRESS', 'no-reply@baaboo.com'),
            'name' => env('MAIL_FROM_NAME', 'Baaboo'),
        ],

        'winners' => [
            'email' => env('MAIL_WINNERS_FROM_ADDRESS', 'winners@badabing.show'),
            'name' => env('MAIL_WINNERS_FROM_NAME', 'Badabing Game Show'),
        ],
    ],

];
