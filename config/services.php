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

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'meta_conversions' => [
        'dataset_id' => env('META_CONVERSIONS_DATASET_ID'),
        'access_token' => env('META_CONVERSIONS_ACCESS_TOKEN'),
        'test_event_code' => env('META_CONVERSIONS_TEST_EVENT_CODE'),
    ],

    'channels' => [
        'base_url' => env('CHANNELS_API_BASE_URL', 'https://api.channels.app'),
        'api_token' => env('CHANNELS_API_TOKEN'),
        'account' => env('CHANNELS_ACCOUNT'),
        'secret' => env('CHANNELS_WEBHOOK_SECRET'),
        'timeout' => (int) env('CHANNELS_API_TIMEOUT', 30),
    ],

    'twilio' => [
        'account_sid' => env('TWILIO_ACCOUNT_SID'),
        'auth_token' => env('TWILIO_AUTH_TOKEN'),
        'api_key_sid' => env('TWILIO_API_KEY_SID'),
        'api_key_secret' => env('TWILIO_API_KEY_SECRET'),
        'twiml_app_sid' => env('TWILIO_TWIML_APP_SID'),
        'caller_id' => env('TWILIO_CALLER_ID'),
        'token_ttl' => env('TWILIO_TOKEN_TTL', 3600),
        'webhook_secret' => env('TWILIO_WEBHOOK_SECRET'),
    ],

];
