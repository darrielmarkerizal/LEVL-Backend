<?php

use Illuminate\Support\Facades\Route;
use Modules\Search\Http\Controllers\SearchController;

Route::prefix('v1')->group(function () {
    // Public search endpoints (no auth required for courses and units)
    Route::get('search', [SearchController::class, 'search'])->name('search.index');
    Route::get('search/global', [SearchController::class, 'globalSearch'])->name('search.global');
    Route::get('search/autocomplete', [SearchController::class, 'autocomplete'])->name('search.autocomplete');

    // Protected search history endpoints (require authentication)
    Route::middleware(['auth:api'])->group(function () {
        Route::get('search/history', [SearchController::class, 'getSearchHistory'])->name('search.history');
        Route::delete('search/history', [SearchController::class, 'clearSearchHistory'])->name('search.history.clear');
        Route::delete('search/history/{id}', [SearchController::class, 'deleteHistoryItem'])->name('search.history.delete');
    });
});
