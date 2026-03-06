<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('form')->group(function () {
    Route::get('/', function () {
        return view('docs.index');
    });
    Route::get('/schemes', function () {
        return view('docs.schemes');
    });
    Route::get('/learning', function () {
        return view('docs.learning');
    });
});

require __DIR__.'/test-browser.php';
