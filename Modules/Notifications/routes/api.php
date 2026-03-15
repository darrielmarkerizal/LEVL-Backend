<?php

use Illuminate\Support\Facades\Route;
use Modules\Notifications\Http\Controllers\NotificationPreferenceController;
use Modules\Notifications\Http\Controllers\NotificationsController;
use Modules\Notifications\Http\Controllers\PostController;
use Modules\Notifications\Http\Controllers\PostMediaController;

Route::middleware(['auth:api'])->prefix('v1')->group(function () {
    Route::apiResource('notifications', NotificationsController::class)->names('notifications');

    // Notification Preferences
    Route::get('notification-preferences', [NotificationPreferenceController::class, 'index'])->name('notification-preferences.index');
    Route::put('notification-preferences', [NotificationPreferenceController::class, 'update'])->name('notification-preferences.update');
    Route::post('notification-preferences/reset', [NotificationPreferenceController::class, 'reset'])->name('notification-preferences.reset');

    // ─── Info & News Management - Shared Routes ────────────────────────────────
    // All authenticated users can view published posts filtered by their role
    Route::prefix('posts')->name('posts.')->group(function () {
        Route::get('/', [PostController::class, 'index'])
            ->middleware('throttle:60,1')
            ->name('index');
        
        Route::get('/pinned', [PostController::class, 'pinned'])
            ->middleware('throttle:60,1')
            ->name('pinned');
        
        Route::get('/{uuid}', [PostController::class, 'show'])
            ->middleware('throttle:60,1')
            ->name('show');
        
        Route::post('/{uuid}/view', [PostController::class, 'markAsViewed'])
            ->middleware('throttle:60,1')
            ->name('view');
    });

    // ─── Info & News Management - Admin Routes ─────────────────────────────────
    // Only Admin users can manage posts
    Route::prefix('admin/posts')->name('admin.posts.')->middleware(['role:Admin'])->group(function () {
        // Core CRUD operations
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

        // Publishing operations
        Route::post('/{uuid}/publish', [PostController::class, 'publish'])
            ->middleware('throttle:60,1')
            ->name('publish');
        
        Route::post('/{uuid}/unpublish', [PostController::class, 'unpublish'])
            ->middleware('throttle:60,1')
            ->name('unpublish');

        // Scheduling operations
        Route::post('/{uuid}/schedule', [PostController::class, 'schedule'])
            ->middleware('throttle:60,1')
            ->name('schedule');
        
        Route::post('/{uuid}/cancel-schedule', [PostController::class, 'cancelSchedule'])
            ->middleware('throttle:60,1')
            ->name('cancel-schedule');

        // Pin operations
        Route::post('/{uuid}/toggle-pin', [PostController::class, 'togglePin'])
            ->middleware('throttle:60,1')
            ->name('toggle-pin');

        // Bulk operations
        Route::post('/bulk-delete', [PostController::class, 'bulkDelete'])
            ->middleware('throttle:10,1')
            ->name('bulk-delete');
        
        Route::post('/bulk-publish', [PostController::class, 'bulkPublish'])
            ->middleware('throttle:10,1')
            ->name('bulk-publish');

        // Trash management
        Route::get('/trash', [PostController::class, 'trash'])
            ->middleware('throttle:60,1')
            ->name('trash');
        
        Route::post('/{uuid}/restore', [PostController::class, 'restore'])
            ->middleware('throttle:60,1')
            ->name('restore');
        
        Route::delete('/{uuid}/force', [PostController::class, 'forceDelete'])
            ->middleware('throttle:60,1')
            ->name('force');

        // Image upload for rich text editor
        Route::post('/upload-image', [PostMediaController::class, 'uploadImage'])
            ->middleware('throttle:10,1')
            ->name('upload-image');
    });
});
