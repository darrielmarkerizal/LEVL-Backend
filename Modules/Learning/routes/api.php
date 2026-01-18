<?php

use Illuminate\Support\Facades\Route;
use Modules\Learning\Http\Controllers\AssignmentController;
use Modules\Learning\Http\Controllers\SubmissionController;

Route::middleware(['auth:api'])->prefix('v1')->scopeBindings()->group(function () {
    // Assignment routes (nested under lesson)
    Route::get(
        'courses/{course:slug}/units/{unit:slug}/lessons/{lesson:slug}/assignments',
        [AssignmentController::class, 'index']
    )->name('lessons.assignments.index');

    Route::middleware('role:Admin|Instructor|Superadmin')->group(function () {
        Route::post(
            'courses/{course:slug}/units/{unit:slug}/lessons/{lesson:slug}/assignments',
            [AssignmentController::class, 'store']
        )->name('lessons.assignments.store');
        Route::put('assignments/{assignment}', [AssignmentController::class, 'update'])->name('assignments.update');
        Route::delete('assignments/{assignment}', [AssignmentController::class, 'destroy'])->name('assignments.destroy');
        Route::put('assignments/{assignment}/publish', [AssignmentController::class, 'publish'])->name('assignments.publish');
        Route::put('assignments/{assignment}/unpublish', [AssignmentController::class, 'unpublish'])->name('assignments.unpublish');
        Route::post('submissions/{submission}/grade', [SubmissionController::class, 'grade'])->name('submissions.grade');
    });

    Route::get('assignments/{assignment}', [AssignmentController::class, 'show'])->name('assignments.show');

    // Submission routes
    Route::get('assignments/{assignment}/submissions', [SubmissionController::class, 'index'])->name('assignments.submissions.index');
    Route::post('assignments/{assignment}/submissions', [SubmissionController::class, 'store'])->name('assignments.submissions.store');
    Route::get('submissions/{submission}', [SubmissionController::class, 'show'])->name('submissions.show');
    Route::put('submissions/{submission}', [SubmissionController::class, 'update'])->name('submissions.update');
});
