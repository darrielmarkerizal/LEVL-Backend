<?php

use Illuminate\Support\Facades\Route;
use Modules\Mail\Http\Controllers\MailController;



Route::group([], function () {
    Route::resource('mail', MailController::class)->names('mail');
});
