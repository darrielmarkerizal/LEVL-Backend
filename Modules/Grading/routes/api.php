<?php

use Illuminate\Support\Facades\Route;
use Modules\Grading\Http\Controllers\GradingController;

Route::middleware(['auth:api'])->prefix('v1')->group(function () {

    Route::middleware(['role:Superadmin|Admin|Instructor'])->group(function () {
        Route::get('grading', [GradingController::class, 'queue'])->name('grading.index');
        Route::get('grading/{submission}', [GradingController::class, 'show'])->name('grading.show');
        Route::post('grading/bulk-release', [GradingController::class, 'bulkReleaseGrades'])->name('grading.bulk.release');
        Route::post('grading/bulk-feedback', [GradingController::class, 'bulkApplyFeedback'])->name('grading.bulk.feedback');
    });

    Route::middleware(['role:Superadmin|Admin|Instructor', 'can:grade,submission'])->prefix('submissions/{submission}/grades')->group(function () {
        Route::get('/', [GradingController::class, 'getGrade'])->name('grading.get');
        Route::post('/', [GradingController::class, 'manualGrade'])->name('grading.store');
        Route::put('draft', [GradingController::class, 'saveDraftGrade'])->name('grading.save-draft');
        Route::patch('/', [GradingController::class, 'overrideGrade'])->name('grading.override');
        Route::patch('release', [GradingController::class, 'releaseGrade'])->name('grading.release');
        Route::patch('return-to-queue', [GradingController::class, 'returnToQueue'])->name('grading.return-to-queue');
        Route::get('status', [GradingController::class, 'gradingStatus'])->name('grading.status');
    });
});
