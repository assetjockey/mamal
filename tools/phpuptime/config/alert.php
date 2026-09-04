<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Monitor alert channels
    |--------------------------------------------------------------------------
    |
    | These are the possible alert channels.
    |
    */

    'channels' => [
        'email' => 'Email',
        'webhook' => 'Webhook',
        'slack' => 'Slack',
        'teams' => 'Microsoft Teams',
        'discord' => 'Discord',
        'flock' => 'Flock',
        'telegram' => 'Telegram',
        'sms' => 'SMS'
    ],

    /*
    |--------------------------------------------------------------------------
    | Monitor alert conditions
    |--------------------------------------------------------------------------
    |
    | These are the possible alert conditions.
    |
    */

    'conditions' => [
        'url_unavailable' => 'URL becomes unavailable',
        'url_text' => 'URL response contains text',
        'url_no_text' => 'URL response does not contain text'
    ]
];
