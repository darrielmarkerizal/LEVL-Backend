<?php

use Illuminate\Support\Facades\Route;
use Modules\Content\Http\Controllers\AnnouncementController;
use Modules\Content\Http\Controllers\ContentStatisticsController;
use Modules\Content\Http\Controllers\CourseAnnouncementController;
use Modules\Content\Http\Controllers\NewsController;
use Modules\Content\Http\Controllers\SearchController;

Route::middleware(['auth:api'])->prefix('v1')->group(function () {
    // Announcements
    Route::prefix('announcements')->group(function () {
        Route::get('/', [AnnouncementController::class, 'index']);
        Route::post('/', [AnnouncementController::class, 'store']);
        Route::get('/{id}', [AnnouncementController::class, 'show']);
        Route::put('/{id}', [AnnouncementController::class, 'update']);
        Route::delete('/{id}', [AnnouncementController::class, 'destroy']);
        Route::post('/{id}/publish', [AnnouncementController::class, 'publish']);
        Route::post('/{id}/schedule', [AnnouncementController::class, 'schedule']);
        Route::post('/{id}/read', [AnnouncementController::class, 'markAsRead']);
    });

    // News
    Route::prefix('news')->group(function () {
        Route::get('/', [NewsController::class, 'index']);
        Route::post('/', [NewsController::class, 'store']);
        Route::get('/trending', [NewsController::class, 'trending']);
        Route::get('/{slug}', [NewsController::class, 'show']);
        Route::put('/{slug}', [NewsController::class, 'update']);
        Route::delete('/{slug}', [NewsController::class, 'destroy']);
        Route::post('/{slug}/publish', [NewsController::class, 'publish']);
        Route::post('/{slug}/schedule', [NewsController::class, 'schedule']);
    });

    // Course Announcements
    Route::prefix('courses/{course}/announcements')->group(function () {
        Route::get('/', [CourseAnnouncementController::class, 'index']);
        Route::post('/', [CourseAnnouncementController::class, 'store']);
    });

    // Statistics
    Route::prefix('content/statistics')->group(function () {
        Route::get('/', [ContentStatisticsController::class, 'index']);
        Route::get('/announcements/{id}', [ContentStatisticsController::class, 'showAnnouncement']);
        Route::get('/news/{slug}', [ContentStatisticsController::class, 'showNews']);
        Route::get('/trending', [ContentStatisticsController::class, 'trending']);
        Route::get('/most-viewed', [ContentStatisticsController::class, 'mostViewed']);
    });

    // Search
    Route::get('/content/search', [SearchController::class, 'search']);

    // Content Approval Workflow
    Route::prefix('content')->group(function () {
        Route::post('/{type}/{id}/submit', [\Modules\Content\Http\Controllers\ContentApprovalController::class, 'submit']);
        Route::post('/{type}/{id}/approve', [\Modules\Content\Http\Controllers\ContentApprovalController::class, 'approve']);
        Route::post('/{type}/{id}/reject', [\Modules\Content\Http\Controllers\ContentApprovalController::class, 'reject']);
        Route::get('/pending-review', [\Modules\Content\Http\Controllers\ContentApprovalController::class, 'pendingReview']);
    });
});
