<?php

use Illuminate\Support\Facades\Route;
use Modules\Operations\Http\Controllers\OperationsController;
use App\Http\Controllers\FileTestController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('operations', OperationsController::class)->names('operations');
});

// File test endpoints (for debugging only - remove in production)
Route::prefix('v1/file-test')->group(function () {
    Route::get('config', [FileTestController::class, 'config']);
    Route::post('upload', [FileTestController::class, 'upload']);
    Route::get('list', [FileTestController::class, 'list']);
    Route::post('check', [FileTestController::class, 'check']);
    Route::delete('delete', [FileTestController::class, 'delete']);
    Route::get('test-s3', [FileTestController::class, 'testS3Operations']);
});
