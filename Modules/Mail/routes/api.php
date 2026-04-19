<?php

use Illuminate\Support\Facades\Route;
use Modules\Mail\Http\Controllers\MailController;



Route::middleware(['auth:api', 'role:Admin|Superadmin'])->prefix('v1')->group(function () {
    Route::apiResource('mail', MailController::class)->names('mail');
});
