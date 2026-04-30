<?php

use Illuminate\Support\Facades\Route;
use Modules\Dashboard\Http\Controllers\DashboardController;

Route::middleware(['auth:api'])->prefix('v1')->group(function () {
    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('index');
        Route::get('/recent-learning', [DashboardController::class, 'recentLearning'])
            ->middleware('role:Student')
            ->name('recent-learning');
        Route::get('/recent-achievements', [DashboardController::class, 'recentAchievements'])->name('recent-achievements');
        Route::get('/recommended-courses', [DashboardController::class, 'recommendedCourses'])->name('recommended-courses');
    });
});
