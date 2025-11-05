<?php

use Illuminate\Support\Facades\Route;
use Modules\Schemes\Http\Controllers\CourseController;
use Modules\Schemes\Http\Controllers\LessonController;
use Modules\Schemes\Http\Controllers\UnitController;

Route::prefix('v1')->group(function () {

    Route::get('courses', [CourseController::class, 'index']);
    Route::get('courses/{course}', [CourseController::class, 'show']);

    Route::middleware(['auth:api', 'role:super-admin|admin'])->group(function () {
        Route::post('courses', [CourseController::class, 'store'])->middleware('can:create,Modules\\Schemes\\Models\\Course');
        Route::put('courses/{course}', [CourseController::class, 'update'])->middleware('can:update,course');
        Route::delete('courses/{course}', [CourseController::class, 'destroy'])->middleware('can:delete,course');
        Route::put('courses/{course}/publish', [CourseController::class, 'publish']);
        Route::put('courses/{course}/unpublish', [CourseController::class, 'unpublish']);
    });

    Route::get('courses/{course}/units', [UnitController::class, 'index']);
    Route::get('courses/{course}/units/{unit}', [UnitController::class, 'show']);

    Route::middleware(['auth:api', 'role:super-admin|admin'])->group(function () {
        Route::post('courses/{course}/units', [UnitController::class, 'store']);
        Route::put('courses/{course}/units/reorder', [UnitController::class, 'reorder']);
        Route::put('courses/{course}/units/{unit}', [UnitController::class, 'update']);
        Route::delete('courses/{course}/units/{unit}', [UnitController::class, 'destroy']);
        Route::put('courses/{course}/units/{unit}/publish', [UnitController::class, 'publish']);
        Route::put('courses/{course}/units/{unit}/unpublish', [UnitController::class, 'unpublish']);
    });

    Route::middleware(['auth:api'])->group(function () {
        Route::get('courses/{course}/units/{unit}/lessons', [LessonController::class, 'index']);
        Route::get('courses/{course}/units/{unit}/lessons/{lesson}', [LessonController::class, 'show']);
    });

    Route::middleware(['auth:api', 'role:super-admin|admin'])->group(function () {
        Route::post('courses/{course}/units/{unit}/lessons', [LessonController::class, 'store']);
        Route::put('courses/{course}/units/{unit}/lessons/{lesson}', [LessonController::class, 'update']);
        Route::delete('courses/{course}/units/{unit}/lessons/{lesson}', [LessonController::class, 'destroy']);
        Route::put('courses/{course}/units/{unit}/lessons/{lesson}/publish', [LessonController::class, 'publish']);
        Route::put('courses/{course}/units/{unit}/lessons/{lesson}/unpublish', [LessonController::class, 'unpublish']);
    });
});
