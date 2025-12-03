<?php

use App\Http\Controllers\FileTestController;
use Illuminate\Support\Facades\Route;
use Modules\Operations\Http\Controllers\OperationsController;

Route::middleware(['auth:api'])->prefix('v1')->group(function () {
    Route::apiResource('operations', OperationsController::class)->names('operations');
});

// File test endpoints - ONLY available in local/testing environments
// These endpoints expose sensitive configuration and file operations
// and must NEVER be accessible in production
if (app()->environment('local', 'testing')) {
    Route::prefix('v1/file-test')->middleware(['auth:api', 'role:Superadmin'])->group(function () {
        Route::get('config', [FileTestController::class, 'config']);
        Route::post('upload', [FileTestController::class, 'upload']);
        Route::get('list', [FileTestController::class, 'list']);
        Route::post('check', [FileTestController::class, 'check']);
        Route::delete('delete', [FileTestController::class, 'delete']);
        Route::get('test-s3', [FileTestController::class, 'testS3Operations']);
        Route::get('test-aws-sdk', [FileTestController::class, 'testAwsSdk']);
    });
}
