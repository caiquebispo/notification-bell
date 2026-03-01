<?php

use CaiqueBispo\NotificationBell\Http\Controllers\PanelNotificationController;
use Illuminate\Support\Facades\Route;

Route::prefix(config('notifications.route.prefix', 'notifications'))
    ->middleware(config('notifications.route.middleware', ['web', 'auth']))
    ->name(config('notifications.route.name', 'notifications.'))
    ->group(function () {
        Route::get('/', [PanelNotificationController::class, 'index'])->name('index');
        Route::get('/{id}', [PanelNotificationController::class, 'show'])->name('show');
        Route::post('/', [PanelNotificationController::class, 'store'])->name('store');
        Route::put('/{notification}', [PanelNotificationController::class, 'update'])->name('update');
        Route::delete('/{notification}', [PanelNotificationController::class, 'destroy'])->name('destroy');
        Route::delete('/destroy/all', [PanelNotificationController::class, 'destroyAll'])->name('destroy.all');
        Route::post('/destroy/selected', [PanelNotificationController::class, 'destroySelected'])->name('destroy.selected');
    });
