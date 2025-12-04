<?php

use Illuminate\Support\Facades\Route;
use Modules\Gamification\Http\Controllers\ChallengeController;
use Modules\Gamification\Http\Controllers\GamificationController;
use Modules\Gamification\Http\Controllers\LeaderboardController;

Route::middleware(['auth:api'])->prefix('v1')->group(function () {
    // Challenges
    Route::get('challenges', [ChallengeController::class, 'index'])->name('challenges.index');
    Route::get('challenges/my', [ChallengeController::class, 'myChallenges'])->name('challenges.my');
    Route::get('challenges/completed', [ChallengeController::class, 'completed'])->name('challenges.completed');
    Route::get('challenges/{challenge}', [ChallengeController::class, 'show'])->name('challenges.show');
    Route::post('challenges/{challenge}/claim', [ChallengeController::class, 'claim'])->name('challenges.claim');

    // Leaderboards
    Route::get('leaderboards', [LeaderboardController::class, 'index'])->name('leaderboards.index');
    Route::get('leaderboards/my-rank', [LeaderboardController::class, 'myRank'])->name('leaderboards.my-rank');

    // Gamification Dashboard
    Route::get('gamification/summary', [GamificationController::class, 'summary'])->name('gamification.summary');
    Route::get('gamification/badges', [GamificationController::class, 'badges'])->name('gamification.badges');
    Route::get('gamification/points-history', [GamificationController::class, 'pointsHistory'])->name('gamification.points-history');
    Route::get('gamification/achievements', [GamificationController::class, 'achievements'])->name('gamification.achievements');
});
