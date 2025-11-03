<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\AuthApiController;

Route::prefix('v1')->group(function () {
    Route::post('/auth/register', [AuthApiController::class, 'register'])->name('auth.register');
    Route::post('/auth/login', [AuthApiController::class, 'login'])->name('auth.login');

    Route::middleware(['auth:api'])->group(function () {
        Route::post('/auth/refresh', [AuthApiController::class, 'refresh'])->name('auth.refresh');
        Route::post('/auth/logout', [AuthApiController::class, 'logout'])->name('auth.logout');
        Route::get('/profile', [AuthApiController::class, 'profile'])->name('auth.profile');
    });
});
