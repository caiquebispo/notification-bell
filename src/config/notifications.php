<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Notification Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the Laravel Notifications package
    |
    */

    'user_model' => App\Models\User::class,

    'badge_limit' => 99,

    'dropdown_limit' => 10,

    'auto_mark_as_read' => true,

    'types' => [
        'info' => [
            'color' => 'blue',
            'icon' => 'info-circle'
        ],
        'success' => [
            'color' => 'green',
            'icon' => 'check-circle'
        ],
        'warning' => [
            'color' => 'yellow',
            'icon' => 'exclamation-triangle'
        ],
        'error' => [
            'color' => 'red',
            'icon' => 'x-circle'
        ]
    ],
    'route' => [
        'prefix' => 'notifications',
        'middleware' => ['web', 'auth'],
        'name' => 'notifications.'
    ],
    'broadcasting' => [
        'enabled' => false,
        'channel' => 'notifications.{user_id}',
        'event' => 'NotificationCreated'
    ],
    'cleanup' => [
        'enabled' => true,
        'days_to_keep' => 30,
        'schedule' => 'daily'
    ]
];
