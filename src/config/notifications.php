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

    'user_table' => 'users',

    /*
    |--------------------------------------------------------------------------
    | User Column Mapping
    |--------------------------------------------------------------------------
    |
    | Map the user model columns used by the notification panel.
    | Useful for systems with different column names (e.g. 'nome' instead of 'name').
    |
    | Example for Brazilian systems:
    |   'user_columns' => ['name' => 'nome'],
    |
    */
    'user_columns' => [
        'name' => 'name',
    ],

    'badge_limit' => 99,

    'dropdown_limit' => 10,

    'polling' => [
        'enabled' => true,
        'interval' => '10s',
    ],

    'auto_mark_as_read' => true,

    'features' => [
        'toasts' => [
            'enabled' => true,
            'duration' => 5000, // 5 seconds
        ],
        'sound' => [
            'enabled' => false,
            'volume' => 0.5, // 0.0 to 1.0
        ],
    ],

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
