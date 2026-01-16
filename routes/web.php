<?php

use Illuminate\Support\Facades\Route;

Route::get("/", function () {
  return view("welcome");
});

// Load test routes
// Load test routes
require __DIR__ . "/test-browser.php";

Route::get('/dev/octane-check', [App\Http\Controllers\DevController::class, 'checkOctane']);

