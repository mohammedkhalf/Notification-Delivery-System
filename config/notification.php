<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Routing Rules
    |--------------------------------------------------------------------------
    |
    | Define rules for routing events to notification channels.
    | Rules are evaluated in order, and all matching rules are applied.
    |
    */

    'routing_rules' => [
        [
            'event_type' => 'USER_REGISTERED',
            'priority' => null,
            'channels' => ['email', 'sms'],
        ],
        [
            'event_type' => 'PAYMENT_COMPLETED',
            'priority' => null,
            'channels' => ['email', 'push'],
        ],
        [
            'event_type' => 'REPORT_GENERATED',
            'priority' => null,
            'channels' => ['email'],
        ],
        [
            'event_type' => null,
            'priority' => 'high',
            'channels' => ['push', 'email'],
        ],
        [
            'event_type' => null,
            'priority' => 'urgent',
            'channels' => ['push', 'email', 'sms'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Retry Configuration
    |--------------------------------------------------------------------------
    |
    | Configure retry behavior for failed notifications.
    |
    */

    'retry' => [
        'max_attempts' => 3,
        'base_delay_seconds' => 60, // Base delay for exponential backoff
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhook Configuration
    |--------------------------------------------------------------------------
    |
    | Default webhook URL for webhook channel.
    |
    */

    'webhook' => [
        'default_url' => env('WEBHOOK_DEFAULT_URL', null),
    ],
];

