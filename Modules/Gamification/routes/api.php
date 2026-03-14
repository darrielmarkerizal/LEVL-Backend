<?php

use Illuminate\Support\Facades\Route;
use Modules\Gamification\Http\Controllers\BadgesController;
use Modules\Gamification\Http\Controllers\GamificationController;
use Modules\Gamification\Http\Controllers\LeaderboardController;
use Modules\Gamification\Http\Controllers\LevelController;
use Modules\Gamification\Http\Controllers\MetricsController;

Route::middleware(['auth:api'])->prefix('v1')->group(function () {

    Route::get('leaderboards', [LeaderboardController::class, 'index'])->name('leaderboards.index');
    Route::get('leaderboards/{userId}/points-history', [LeaderboardController::class, 'userPointsHistory'])->name('leaderboards.user-points-history');

    // Metrics endpoint for monitoring (Prometheus/Grafana)
    Route::get('metrics', [MetricsController::class, 'index'])
        ->middleware(['role:Superadmin'])
        ->name('metrics.index');

    Route::prefix('badges')->name('badges.')->group(function () {
        Route::get('/', [BadgesController::class, 'index'])->name('index');
        Route::get('/{badge}', [BadgesController::class, 'show'])->name('show');

        Route::middleware(['role:Superadmin'])->group(function () {
            Route::post('/', [BadgesController::class, 'store'])->name('store');
            Route::put('/{badge}', [BadgesController::class, 'update'])->name('update');
            Route::delete('/{badge}', [BadgesController::class, 'destroy'])->name('destroy');
        });
    });

    Route::prefix('levels')->name('levels.')->group(function () {
        Route::get('/', [LevelController::class, 'index'])->name('index');
        Route::get('/progression', [LevelController::class, 'progression'])->name('progression');
        Route::post('/calculate', [LevelController::class, 'calculate'])->name('calculate');
        
        Route::middleware(['role:Superadmin'])->group(function () {
            Route::post('/sync', [LevelController::class, 'sync'])->name('sync');
            Route::put('/{id}', [LevelController::class, 'update'])->name('update');
            Route::get('/statistics', [LevelController::class, 'statistics'])->name('statistics');
        });
    });

    Route::prefix('user')->name('user.')->group(function () {

        Route::get('rank', [LeaderboardController::class, 'myRank'])->name('gamification.rank');
        Route::get('gamification-summary', [GamificationController::class, 'summary'])->name('gamification.summary');

        Route::get('badges', [GamificationController::class, 'badges'])->name('gamification.badges');
        Route::get('points-history', [GamificationController::class, 'pointsHistory'])->name('gamification.points-history');
        Route::get('milestones', [GamificationController::class, 'milestones'])->name('gamification.milestones');
        Route::get('level', [LevelController::class, 'userLevel'])->name('gamification.level');
        Route::get('daily-xp-stats', [LevelController::class, 'dailyXpStats'])->name('gamification.daily-xp-stats');
        Route::get('levels/{slug}', [GamificationController::class, 'unitLevels'])->name('gamification.unit-levels');
    });
});
