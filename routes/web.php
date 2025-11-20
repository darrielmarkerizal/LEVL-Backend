<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OpenApiController;

Route::get('/', function () {
    return view('welcome');
});

// OpenAPI Specification endpoint for Scalar documentation
Route::get('/api-docs/openapi.json', [OpenApiController::class, 'index'])->name('openapi.spec');
