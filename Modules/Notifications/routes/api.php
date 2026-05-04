<?php

use Illuminate\Support\Facades\Route;
use Modules\Notifications\Http\Controllers\NotificationDeviceController;
use Modules\Notifications\Http\Controllers\NotificationPreferenceController;
use Modules\Notifications\Http\Controllers\NotificationsController;
use Modules\Notifications\Http\Controllers\PostController;

Route::middleware(['auth:api'])->prefix('v1')->group(function () {
    Route::post('notifications/read-all', [NotificationsController::class, 'readAll'])->name('notifications.read-all');
    Route::apiResource('notifications', NotificationsController::class)->except(['create', 'edit'])->names('notifications');

    Route::get('notification-preferences', [NotificationPreferenceController::class, 'index'])->name('notification-preferences.index');
    Route::put('notification-preferences', [NotificationPreferenceController::class, 'update'])->name('notification-preferences.update');
    Route::post('notification-preferences/reset', [NotificationPreferenceController::class, 'reset'])->name('notification-preferences.reset');
    Route::post('notification-device/fcm-token', [NotificationDeviceController::class, 'store'])->name('notification-device.store');
    Route::delete('notification-device/fcm-token', [NotificationDeviceController::class, 'destroy'])->name('notification-device.destroy');

    Route::prefix('admin/posts')->name('admin.posts.')->middleware(['role:Admin|Superadmin'])->group(function () {
        Route::post('/', [PostController::class, 'store'])
            ->middleware('throttle:60,1')
            ->name('store');

        Route::get('/', [PostController::class, 'index'])
            ->middleware('throttle:60,1')
            ->name('index');

        Route::get('/{uuid}', [PostController::class, 'show'])
            ->middleware('throttle:60,1')
            ->name('show');

        Route::put('/{uuid}', [PostController::class, 'update'])
            ->middleware('throttle:60,1')
            ->name('update');

        Route::delete('/{uuid}', [PostController::class, 'destroy'])
            ->middleware('throttle:60,1')
            ->name('destroy');

        Route::post('/{uuid}/publish', [PostController::class, 'publish'])
            ->middleware('throttle:60,1')
            ->name('publish');

        Route::post('/{uuid}/unpublish', [PostController::class, 'unpublish'])
            ->middleware('throttle:60,1')
            ->name('unpublish');



        Route::post('/bulk-delete', [PostController::class, 'bulkDelete'])
            ->middleware('throttle:10,1')
            ->name('bulk-delete');

        Route::post('/bulk-publish', [PostController::class, 'bulkPublish'])
            ->middleware('throttle:10,1')
            ->name('bulk-publish');

    });
});
