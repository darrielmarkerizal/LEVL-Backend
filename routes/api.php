<?php

use Illuminate\Support\Facades\Route;

// Benchmark API endpoint (stateless, no session)
Route::get('/benchmark-api', [App\Http\Controllers\DevController::class, 'benchmarkApi']);
