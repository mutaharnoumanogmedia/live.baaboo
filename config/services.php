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


    'activecampaign'    => [
        'url'           => env('ACTIVECAMPAIGN_API_URL'),
        'key'           => env('ACTIVECAMPAIGN_API_KEY'),
        'lists'         => [
            [
                'id'   => env('ACTIVECAMPAIGN_LIST_ALL'),
                'slug' => 'all',
                'name' => 'MASTER CONTACTS – ALL AFFILIATE CONTACTS',
            ],
            [
                'id'   => env('ACTIVECAMPAIGN_LIST_AFFILIATE'),
                'slug' => 'affiliate',
                'name' => 'REGISTERED AFFILIATES',
            ],
            [
                'id'   => env('ACTIVECAMPAIGN_LIST_LEAD'),
                'slug' => 'lead',
                'name' => 'BOM REGISTRANTS',
            ],
            [
                'id'   => env('ACTIVECAMPAIGN_LIST_GAMESHOW'),
                'slug' => 'gameshow',
                'name' => 'GAMESHOW REGISTRANTS',
            ],
        ],
        'tags'          => [
            [
                'id'   => env('ACTIVECAMPAIGN_TAG_AFFILIATE'),
                'slug' => 'affiliate',
                'name' => 'AFFILIATE',
            ],
            [
                'id'   => env('ACTIVECAMPAIGN_TAG_AFFILIATE_PRO'),
                'slug' => 'affiliate-pro',
                'name' => 'AFFILIATE-PRO',
            ],
            [
                'id'   => env('ACTIVECAMPAIGN_TAG_AFFILIATE_PRO_PLUS'),
                'slug' => 'affiliate-pro-plus',
                'name' => 'AFFILIATE-PRO-PLUS',
            ],
            [
                'id'   => env('ACTIVECAMPAIGN_TAG_AFF_PRO_YEARLY_PAY'),
                'slug' => 'aff-pro-yearly-pay',
                'name' => 'AFF_PRO_YEARLY-PAY',
            ],
            [
                'id'   => env('ACTIVECAMPAIGN_TAG_OG_PACK'),
                'slug' => 'og-pack',
                'name' => 'OG-PACK',
            ],
            [
                'id'   => env('ACTIVECAMPAIGN_TAG_SIGNUP', 2492),
                'slug' => 'lead-signup',
                'name' => 'AFF_LP-SIGNUP',
            ],
            [
                'id'   => env('ACTIVECAMPAIGN_TAG_START_WATCHING', 2493),
                'slug' => 'start-watching',
                'name' => 'AFF_PR-START',
            ],
            [
                'id'   => env('ACTIVECAMPAIGN_TAG_WATCHED', 2494),
                'slug' => 'watched',
                'name' => 'AFF_PR-WATCH',
            ],
            [
                'id'   => env('ACTIVECAMPAIGN_UNSUBSCRIBED', 2496),
                'slug' => 'affiliate-unsubscribed',
                'name' => 'AFF_PR-UNSUBSCRIBED',
            ],
            [
                'id'   => env('ACTIVECAMPAIGN_TAG_AFF_INACTIVE', 2510),
                'slug' => 'affiliate-inactive',
                'name' => 'AFF_INACTIVE',
            ],
            [
                'id'   => env('ACTIVECAMPAIGN_TAG_AFF_ACTIVE', 2511),
                'slug' => 'affiliate-active',
                'name' => 'AFF_ACTIVE',
            ],
            [
                'id'   => env('ACTIVECAMPAIGN_TAG_AFF_ONBOARDING_WATCH', 2512),
                'slug' => 'affiliate-onboarding-watched',
                'name' => 'AFF_ONBOARDING_WATCH',
            ],
            [
                'id'   => env('ACTIVECAMPAIGN_TAG_AFF_ONBOARDING_START', 2513),
                'slug' => 'affiliate-onboarding-start',
                'name' => 'AFF_ONBOARDING_START',
            ],
            [
                'id'   => env('ACTIVECAMPAIGN_TAG_AFF_FREE_GIFT_USED', 2514),
                'slug' => 'affiliate-free-gift-used',
                'name' => 'AFF_FREE-GIFT_USED',
            ],
            [
                'id'   => env('ACTIVECAMPAIGN_TAG_AFF_LEAD_GENERATED', 2515),
                'slug' => 'affiliate-lead-generated',
                'name' => 'AFF_LEAD_GENERATED',
            ],
            [
                'id'   => env('ACTIVECAMPAIGN_TAG_AFF_VBW_PUR', 2516),
                'slug' => 'affiliate-vision-board-workshop-purchased',
                'name' => 'AFF_VBW_PUR',
            ],
            [
                'id'   => env('ACTIVECAMPAIGN_TAG_AFF_BB_BOOKS_PUR', 2517),
                'slug' => 'affiliate-baaboo-books-purchased',
                'name' => 'AFF_BB-BOOKS_PUR',
            ],
            [
                'id'   => env('ACTIVECAMPAIGN_TAG_AFF_PARTNER_GENERATED', 2518),
                'slug' => 'affiliate-partner-generated',
                'name' => 'AFF_PARTNER_GENERATED',
            ],
            [
                'id'   => env('ACTIVECAMPAIGN_TAG_GAMESHOW_REGISTERED', 2534),
                'slug' => 'gameshow_registered',
                'name' => 'gameshow_registered',
            ],
            [
                'id'   => env('ACTIVECAMPAIGN_TAG_GAMESHOW_ATTENDED', 2535),
                'slug' => 'gameshow_attended',
                'name' => 'gameshow_attended',
            ],
            [
                'id'   => env('ACTIVECAMPAIGN_TAG_GAMESHOW_ATTENDED_GENERAL', 2540),
                'slug' => 'gameshow_attended_general',
                'name' => 'gameshow_attended_general',
            ],
        ],

        'custom_fields' => [
            'contact' => [
                ['title' => 'Country', 'perstag' => 'COUNTRY', 'type' => 'text'],
                ['title' => 'Creation Date / Time', 'perstag' => 'CREATION_DATE_TIME', 'type' => 'date'],
                ['title' => 'Registration Date / Time', 'perstag' => 'REGISTRATION_DATE_TIME', 'type' => 'date'],
                ['title' => 'Partner Username', 'perstag' => 'PARTNER_USERNAME', 'type' => 'text'],
                ['title' => 'Partner Referral Link', 'perstag' => 'PARTNER_REFERRAL_LINK', 'type' => 'text'],
                ['title' => 'Affiliate Rank', 'perstag' => 'RANK', 'type' => 'text'],
                ['title' => 'Affiliate Username', 'perstag' => 'USERNAME', 'type' => 'text'],
                ['title' => 'Affiliate Präsentation Link', 'perstag' => 'AFFILIATE_PRSENTATION_LINK', 'type' => 'text'],
                ['title' => 'Custom Field Magic Link (Live)', 'perstag' => 'CUSTOM_FIELD_MAGIC_LINK_LIVE', 'type' => 'text'],
                ['title' => 'Referral Link (Live)', 'perstag' => 'REFERRAL_LINK_LIVE', 'type' => 'text'],
            ],
        ],
    ],

];
