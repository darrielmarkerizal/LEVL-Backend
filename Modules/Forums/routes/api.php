<?php

use Illuminate\Support\Facades\Route;
use Modules\Forums\Http\Controllers\ThreadController;
use Modules\Forums\Http\Controllers\ReplyController;
use Modules\Forums\Http\Controllers\ReactionController;
use Modules\Forums\Http\Controllers\ForumStatisticsController;
use Modules\Forums\Http\Middleware\CheckForumAccess;

Route::middleware(['auth:api'])->prefix('v1')->group(function () {

    Route::prefix('schemes/{scheme}/forum')
        ->middleware(CheckForumAccess::class)
        ->group(function () {
        
        
        Route::get('threads', [ThreadController::class, 'index']); 
        Route::post('threads', [ThreadController::class, 'store']);
        Route::get('threads/{thread}', [ThreadController::class, 'show']);
        Route::put('threads/{thread}', [ThreadController::class, 'update']);
        Route::delete('threads/{thread}', [ThreadController::class, 'destroy']);
        
        
        Route::patch('threads/{thread}', [ThreadController::class, 'updateState']); 
        
        
        
        Route::post('threads/{thread}/replies', [ReplyController::class, 'store']);
        Route::put('replies/{reply}', [ReplyController::class, 'update']);
        Route::delete('replies/{reply}', [ReplyController::class, 'destroy']);
        Route::patch('replies/{reply}', [ReplyController::class, 'updateState']);
        
        
        
        Route::post('threads/{thread}/reactions', [ReactionController::class, 'store']);
        Route::delete('threads/{thread}/reactions/{type}', [ReactionController::class, 'destroy']);
        
        Route::post('replies/{reply}/reactions', [ReactionController::class, 'store']);
        Route::delete('replies/{reply}/reactions/{type}', [ReactionController::class, 'destroy']);
        
        
        Route::get('statistics', [ForumStatisticsController::class, 'index']);
        Route::get('statistics/me', [ForumStatisticsController::class, 'show']);
    });
});