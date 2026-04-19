<?php

use Illuminate\Support\Facades\Route;
use Modules\Search\Http\Controllers\SearchController;

Route::prefix('v1')->group(function () {
    
    Route::get('search', [SearchController::class, 'search'])->name('search.index');
    Route::get('search/global', [SearchController::class, 'globalSearch'])->name('search.global');
    Route::get('search/autocomplete', [SearchController::class, 'autocomplete'])->name('search.autocomplete');

    
    Route::middleware(['auth:api'])->group(function () {
        Route::get('search/history', [SearchController::class, 'getSearchHistory'])->name('search.history');
        Route::delete('search/history', [SearchController::class, 'clearSearchHistory'])->name('search.history.clear');
        Route::delete('search/history/{id}', [SearchController::class, 'deleteHistoryItem'])->name('search.history.delete');
    });
});
