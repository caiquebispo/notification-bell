<?php

namespace CaiqueBispo\NotificationBell\Providers;

use CaiqueBispo\NotificationBell\Console\Commands\CleanupNotificationsCommand;
use CaiqueBispo\NotificationBell\Console\Commands\SendBulkNotificationsCommand;
use CaiqueBispo\NotificationBell\Http\Controllers\AssetController;
use CaiqueBispo\NotificationBell\Livewire\NotificationBell;
use CaiqueBispo\NotificationBell\Livewire\NotificationHistory;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class NotificationServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'notification-bell');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'notification-bell');
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        Livewire::component('notification-bell', NotificationBell::class);
        Livewire::component('notification-bell-history', NotificationHistory::class);

        // <head>: @notificationBellStyles injeta o CSS servido pelo próprio
        // pacote — nenhuma publicação de assets ou build (npm) é necessária.
        Blade::directive('notificationBellStyles', function () {
            return "<?php echo '<link rel=\"stylesheet\" href=\"' . e(" . AssetController::class . "::url('notification-bell.css')) . '\">'; ?>";
        });

        $this->publishes([
            __DIR__ . '/../config/notifications.php' => config_path('notifications.php'),
        ], 'notification-bell-config');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/notification-bell'),
        ], 'notification-bell-views');

        $this->publishes([
            __DIR__ . '/../resources/lang' => $this->app->langPath('vendor/notification-bell'),
        ], 'notification-bell-lang');

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
