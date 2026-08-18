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

    /*
    |--------------------------------------------------------------------------
    | Locale
    |--------------------------------------------------------------------------
    |
    | Idioma dos textos do componente e do painel. null segue o locale do app
    | (fallback padrão: inglês). Force um idioma com 'pt_BR' ou 'en',
    | independente do locale da aplicação.
    |
    */
    'locale' => null,

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

    /*
    |--------------------------------------------------------------------------
    | Theme
    |--------------------------------------------------------------------------
    |
    | Visual customization applied through CSS variables. Any valid CSS color
    | works here (hex, rgb, oklch, or a var() reference to your design system).
    |
    */
    'theme' => [
        // auto: segue apenas marcadores explícitos do site (.dark,
        // [data-theme="dark"], [data-bs-theme="dark"]). system: também segue
        // o prefers-color-scheme do SO. dark/light: força o modo.
        'mode' => 'auto',
        'primary' => '#3b82f6',
        'badge_background' => '#ef4444',
        'badge_text' => '#ffffff',
        'badge_style' => 'count',      // count | dot | pulse
        'badge_position' => 'top-right', // top-right | top-left | bottom-right | bottom-left
        'radius' => '1rem',
        'dropdown_width' => '20rem',
        'toast_position' => 'bottom-right', // top-right | top-left | bottom-right | bottom-left | top-center | bottom-center
        'bell_icon' => null,           // caminho de uma view Blade com o SVG do sino
        'item_view' => null,           // caminho de uma view Blade para renderizar cada item
    ],

    /*
    |--------------------------------------------------------------------------
    | User Preferences
    |--------------------------------------------------------------------------
    |
    | Lets each user control their own notification experience through a panel
    | inside the bell. Values here are only the defaults for new users.
    |
    */
    'preferences' => [
        'enabled' => true,
        'allow_sound_control' => true,
        'allow_toast_control' => true,
        'allow_category_control' => true,
        'allow_snooze' => true,
        'allow_quiet_hours' => true,
        'snooze_options' => [
            '1h' => 60,
            '4h' => 240,
            '8h' => 480,
            '24h' => 1440,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Categories
    |--------------------------------------------------------------------------
    |
    | Optional grouping above notification types. Users can mute categories
    | individually from the preferences panel.
    |
    */
    'categories' => [
        // 'orders' => ['label' => 'Pedidos', 'icon' => 'shopping-bag'],
        // 'system' => ['label' => 'Sistema', 'icon' => 'cog'],
    ],

    'features' => [
        'toasts' => [
            'enabled' => true,
            'duration' => 5000, // 5 seconds
            'max_stack' => 3,   // quantos toasts simultâneos na tela
        ],
        'sound' => [
            'enabled' => false,
            'volume' => 0.5, // 0.0 to 1.0
            'file' => null,  // URL de um áudio próprio; nulo usa a Web Audio API
        ],
        'pin' => true,
        'archive' => true,
        'clear_all' => true,
        'undo_delete' => [
            'enabled' => true,
            'window' => 8000, // ms que o botão "Desfazer" fica disponível
        ],
        'grouping' => [
            'enabled' => true,
            'min_size' => 3, // a partir de quantas notificações agrupar por group_key
        ],
        'history_page' => true,
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

    /*
    |--------------------------------------------------------------------------
    | REST API
    |--------------------------------------------------------------------------
    |
    | JSON endpoints so mobile apps and SPAs can consume the same system.
    |
    */
    'api' => [
        'enabled' => false,
        'prefix' => 'api/notifications',
        'middleware' => ['api', 'auth:sanctum'],
        'name' => 'notifications.api.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Broadcasting
    |--------------------------------------------------------------------------
    |
    | When enabled the bell listens over websockets (Echo / Reverb / Pusher)
    | and automatically falls back to polling if the connection is unavailable.
    |
    */
    'broadcasting' => [
        'enabled' => false,
        'channel' => 'notifications.{user_id}',
        'event' => 'NotificationCreated',
        'private' => true,
        'fallback_to_polling' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Delivery Guards
    |--------------------------------------------------------------------------
    |
    | Protects users from notification floods caused by loops or bugs.
    |
    */
    'deduplication' => [
        'enabled' => true,
        'window' => 300, // segundos em que uma dedup_key repetida é descartada
    ],

    'rate_limit' => [
        'enabled' => true,
        'max_per_minute' => 30, // por usuário
    ],

    'cleanup' => [
        'enabled' => true,
        'days_to_keep' => 30,
        'schedule' => 'daily',
        'keep_pinned' => true,
        'keep_archived' => false,
    ]
];
