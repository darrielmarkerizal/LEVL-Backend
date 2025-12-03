<?php

use Illuminate\Support\Facades\Route;
use Modules\Forums\Http\Controllers\ReactionController;
use Modules\Forums\Http\Controllers\ReplyController;
use Modules\Forums\Http\Controllers\ThreadController;
use Modules\Forums\Http\Middleware\CheckForumAccess;

Route::middleware(['auth:api'])->prefix('v1')->group(function () {

    // Thread routes for a specific scheme
    Route::prefix('schemes/{scheme}/forum')->middleware(CheckForumAccess::class)->group(function () {
        // Thread listing and search
        Route::get('threads', [ThreadController::class, 'index']);
        Route::get('threads/search', [ThreadController::class, 'search']);

        // Thread CRUD
        Route::post('threads', [ThreadController::class, 'store']);
        Route::get('threads/{thread}', [ThreadController::class, 'show']);
        Route::put('threads/{thread}', [ThreadController::class, 'update']);
        Route::delete('threads/{thread}', [ThreadController::class, 'destroy']);

        // Thread moderation
        Route::post('threads/{thread}/pin', [ThreadController::class, 'pin']);
        Route::post('threads/{thread}/close', [ThreadController::class, 'close']);
    });

    // Reply routes
    Route::prefix('forum')->group(function () {
        // Reply CRUD
        Route::post('threads/{thread}/replies', [ReplyController::class, 'store']);
        Route::put('replies/{reply}', [ReplyController::class, 'update']);
        Route::delete('replies/{reply}', [ReplyController::class, 'destroy']);

        // Reply moderation
        Route::post('replies/{reply}/accept', [ReplyController::class, 'accept']);
    });

    // Reaction routes
    Route::prefix('forum')->group(function () {
        Route::post('threads/{thread}/reactions', [ReactionController::class, 'toggleThreadReaction']);
        Route::post('replies/{reply}/reactions', [ReactionController::class, 'toggleReplyReaction']);
    });

    // Statistics routes
    Route::prefix('schemes/{scheme}/forum')->middleware(CheckForumAccess::class)->group(function () {
        Route::get('statistics', [ForumStatisticsController::class, 'index']);
        Route::get('statistics/me', [ForumStatisticsController::class, 'userStats']);
    });
});
