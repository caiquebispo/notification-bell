<?php

use CaiqueBispo\NotificationBell\Http\Controllers\AssetController;
use CaiqueBispo\NotificationBell\Http\Controllers\NotificationApiController;
use CaiqueBispo\NotificationBell\Http\Controllers\PanelNotificationController;
use Illuminate\Support\Facades\Route;

// Assets do pacote (CSS/JS): servidos direto do vendor, sem publicação
// nem build. Rota pública — os arquivos são estáticos.
Route::get('notification-bell/assets/{asset}', AssetController::class)
    ->where('asset', '[a-z0-9\-\.]+')
    ->middleware('web')
    ->name('notification-bell.asset');

Route::prefix(config('notifications.route.prefix', 'notifications'))
    ->middleware(config('notifications.route.middleware', ['web', 'auth']))
    ->name(config('notifications.route.name', 'notifications.'))
    ->group(function () {
        if (config('notifications.features.history_page', true)) {
            Route::get('/history', fn () => view('notification-bell::history-page'))->name('history');
        }

        Route::get('/', [PanelNotificationController::class, 'index'])->name('index');
        Route::get('/{id}', [PanelNotificationController::class, 'show'])->whereNumber('id')->name('show');
        Route::post('/', [PanelNotificationController::class, 'store'])->name('store');
        Route::put('/{notification}', [PanelNotificationController::class, 'update'])->name('update');
        Route::delete('/{notification}', [PanelNotificationController::class, 'destroy'])->name('destroy');
        Route::delete('/destroy/all', [PanelNotificationController::class, 'destroyAll'])->name('destroy.all');
        Route::post('/destroy/selected', [PanelNotificationController::class, 'destroySelected'])->name('destroy.selected');
    });

if (config('notifications.api.enabled', false)) {
    Route::prefix(config('notifications.api.prefix', 'api/notifications'))
        ->middleware(config('notifications.api.middleware', ['api', 'auth:sanctum']))
        ->name(config('notifications.api.name', 'notifications.api.'))
        ->group(function () {
            Route::get('/', [NotificationApiController::class, 'index'])->name('index');
            Route::get('/unread-count', [NotificationApiController::class, 'unreadCount'])->name('unread-count');
            Route::get('/stats', [NotificationApiController::class, 'stats'])->name('stats');
            Route::post('/read-all', [NotificationApiController::class, 'markAllAsRead'])->name('read-all');
            Route::post('/{id}/read', [NotificationApiController::class, 'markAsRead'])->whereNumber('id')->name('read');
            Route::post('/{id}/archive', [NotificationApiController::class, 'archive'])->whereNumber('id')->name('archive');
            Route::post('/{id}/unarchive', [NotificationApiController::class, 'unarchive'])->whereNumber('id')->name('unarchive');
            Route::post('/{id}/pin', [NotificationApiController::class, 'togglePin'])->whereNumber('id')->name('pin');
            Route::post('/{id}/restore', [NotificationApiController::class, 'restore'])->whereNumber('id')->name('restore');
            Route::delete('/{id}', [NotificationApiController::class, 'destroy'])->whereNumber('id')->name('destroy');
            Route::get('/preferences', [NotificationApiController::class, 'preferences'])->name('preferences');
            Route::put('/preferences', [NotificationApiController::class, 'updatePreferences'])->name('preferences.update');
        });
}
