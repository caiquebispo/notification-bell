<?php

namespace CaiqueBispo\NotificationBell\Providers;

use Livewire\Livewire;

use Illuminate\Support\ServiceProvider;
use CaiqueBispo\NotificationBell\Livewire\NotificationBell;
use CaiqueBispo\NotificationBell\Console\Commands\CleanupNotificationsCommand;
use CaiqueBispo\NotificationBell\Console\Commands\SendBulkNotificationsCommand;

class NotificationServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'notification-bell');
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        Livewire::component('notification-bell', NotificationBell::class);

        $this->publishes([
            __DIR__ . '/../config/notifications.php' => config_path('notifications.php'),
        ], 'notification-bell-config');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/notification-bell'),
        ], 'notification-bell-views');

        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'notification-bell-migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([
                CleanupNotificationsCommand::class,
                SendBulkNotificationsCommand::class,
            ]);
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/notifications.php', 'notifications');
    }
}
