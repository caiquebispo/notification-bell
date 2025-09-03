<?php

use CaiqueBispo\NotificationBell\Http\Controllers\PanelNotificationController;
use Illuminate\Support\Facades\Route;

Route::prefix('notifications')->middleware(['web', 'auth'])->group(function () {
    Route::get('/', [PanelNotificationController::class, 'index'])->name('notifications.index');
    Route::get('/{id}', [PanelNotificationController::class, 'show'])->name('notifications.show');
    Route::post('/', [PanelNotificationController::class, 'store'])->name('notifications.store');
    Route::put('/{notification}', [PanelNotificationController::class, 'update'])->name('notifications.update');
    Route::delete('/{notification}', [PanelNotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::delete('/destroy/all', [PanelNotificationController::class, 'destroyAll'])->name('notifications.destroy.all');
});
