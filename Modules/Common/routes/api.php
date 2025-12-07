<?php

use App\Http\Controllers\Api\MasterDataCrudController;
use Illuminate\Support\Facades\Route;
use Modules\Common\Http\Controllers\CategoriesController;
use Modules\Common\Http\Controllers\MasterDataController;
use Modules\Schemes\Http\Controllers\TagController;

Route::prefix('v1')->group(function () {

    // Master Data routes
    Route::prefix('master-data')->name('master-data.')->group(function () {
        // Get all master data types (from database)
        Route::get('/', [MasterDataCrudController::class, 'types'])->name('index');

        // ==================== DATABASE MASTER DATA (CRUD) ====================

        // Categories CRUD (legacy endpoints)
        Route::get('categories', [CategoriesController::class, 'index'])->name('categories.index');
        Route::get('categories/{category}', [CategoriesController::class, 'show'])->name('categories.show');
        Route::middleware(['auth:api', 'role:Superadmin'])->group(function () {
            Route::post('categories', [CategoriesController::class, 'store'])->name('categories.store');
            Route::put('categories/{category}', [CategoriesController::class, 'update'])->name('categories.update');
            Route::delete('categories/{category}', [CategoriesController::class, 'destroy'])->name('categories.destroy');
        });

        // Tags CRUD (legacy endpoints)
        Route::get('tags', [TagController::class, 'index'])->name('tags.index');
        Route::get('tags/{tag:slug}', [TagController::class, 'show'])->name('tags.show');
        Route::middleware(['auth:api', 'role:Superadmin|Admin'])->group(function () {
            Route::post('tags', [TagController::class, 'store'])->name('tags.store');
            Route::put('tags/{tag:slug}', [TagController::class, 'update'])->name('tags.update');
            Route::delete('tags/{tag:slug}', [TagController::class, 'destroy'])->name('tags.destroy');
        });

        // ==================== DYNAMIC CRUD FOR ALL MASTER DATA TYPES ====================
        // Supports: filter[is_active], filter[value], filter[label], sort, per_page, all

        // Public read endpoints
        Route::get('{type}/items', [MasterDataCrudController::class, 'index'])->name('items.index');
        Route::get('{type}/items/{id}', [MasterDataCrudController::class, 'show'])->name('items.show');

        // Admin write endpoints (Superadmin only)
        Route::middleware(['auth:api', 'role:Superadmin'])->group(function () {
            Route::post('{type}/items', [MasterDataCrudController::class, 'store'])->name('items.store');
            Route::put('{type}/items/{id}', [MasterDataCrudController::class, 'update'])->name('items.update');
            Route::delete('{type}/items/{id}', [MasterDataCrudController::class, 'destroy'])->name('items.destroy');
        });

        // ==================== ENUM MASTER DATA (READ-ONLY - BACKWARD COMPATIBLE) ====================

        // Auth
        Route::get('user-status', [MasterDataController::class, 'userStatuses'])->name('user-status');
        Route::get('roles', [MasterDataController::class, 'roles'])->name('roles');

        // Schemes
        Route::get('course-status', [MasterDataController::class, 'courseStatuses'])->name('course-status');
        Route::get('course-types', [MasterDataController::class, 'courseTypes'])->name('course-types');
        Route::get('enrollment-types', [MasterDataController::class, 'enrollmentTypes'])->name('enrollment-types');
        Route::get('level-tags', [MasterDataController::class, 'levelTags'])->name('level-tags');
        Route::get('progression-modes', [MasterDataController::class, 'progressionModes'])->name('progression-modes');
        Route::get('content-types', [MasterDataController::class, 'contentTypes'])->name('content-types');

        // Enrollments
        Route::get('enrollment-status', [MasterDataController::class, 'enrollmentStatuses'])->name('enrollment-status');
        Route::get('progress-status', [MasterDataController::class, 'progressStatuses'])->name('progress-status');

        // Learning
        Route::get('assignment-status', [MasterDataController::class, 'assignmentStatuses'])->name('assignment-status');
        Route::get('submission-status', [MasterDataController::class, 'submissionStatuses'])->name('submission-status');
        Route::get('submission-types', [MasterDataController::class, 'submissionTypes'])->name('submission-types');

        // Content
        Route::get('content-status', [MasterDataController::class, 'contentStatuses'])->name('content-status');
        Route::get('priorities', [MasterDataController::class, 'priorities'])->name('priorities');
        Route::get('target-types', [MasterDataController::class, 'targetTypes'])->name('target-types');

        // Gamification
        Route::get('challenge-types', [MasterDataController::class, 'challengeTypes'])->name('challenge-types');
        Route::get('challenge-assignment-status', [MasterDataController::class, 'challengeAssignmentStatuses'])->name('challenge-assignment-status');
        Route::get('challenge-criteria-types', [MasterDataController::class, 'challengeCriteriaTypes'])->name('challenge-criteria-types');
        Route::get('badge-types', [MasterDataController::class, 'badgeTypes'])->name('badge-types');
        Route::get('point-source-types', [MasterDataController::class, 'pointSourceTypes'])->name('point-source-types');
        Route::get('point-reasons', [MasterDataController::class, 'pointReasons'])->name('point-reasons');

        // Notifications
        Route::get('notification-types', [MasterDataController::class, 'notificationTypes'])->name('notification-types');
        Route::get('notification-channels', [MasterDataController::class, 'notificationChannels'])->name('notification-channels');
        Route::get('notification-frequencies', [MasterDataController::class, 'notificationFrequencies'])->name('notification-frequencies');

        // Grading
        Route::get('grade-status', [MasterDataController::class, 'gradeStatuses'])->name('grade-status');
        Route::get('grade-source-types', [MasterDataController::class, 'gradeSourceTypes'])->name('grade-source-types');

        // Common
        Route::get('category-status', [MasterDataController::class, 'categoryStatuses'])->name('category-status');
        Route::get('setting-types', [MasterDataController::class, 'settingTypes'])->name('setting-types');
    });
});
