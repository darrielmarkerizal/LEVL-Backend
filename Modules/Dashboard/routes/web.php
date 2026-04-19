<?php

use Illuminate\Support\Facades\Route;
use Modules\Dashboard\Http\Controllers\DashboardController;



Route::group([], function () {
    Route::resource('dashboard', DashboardController::class)->names('dashboard');
});
