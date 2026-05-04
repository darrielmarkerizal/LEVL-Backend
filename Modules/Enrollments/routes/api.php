<?php

use Illuminate\Support\Facades\Route;
use Modules\Enrollments\Http\Controllers\EnrollmentsController;

Route::middleware(['auth:api'])->prefix('v1')->group(function () {
    
    Route::middleware(['throttle:enrollment'])->group(function () {
        Route::post('courses/{course:slug}/enroll', [EnrollmentsController::class, 'store'])
            ->name('courses.enrollments.store')
            ->middleware('role:Student');
        Route::post('enrollments/create', [EnrollmentsController::class, 'createManual'])
            ->name('enrollments.create-manual')
            ->middleware('role:Superadmin|Admin');
        Route::post('courses/{course:slug}/cancel', [EnrollmentsController::class, 'cancel'])->name('courses.enrollments.cancel');
        Route::post('courses/{course:slug}/withdraw', [EnrollmentsController::class, 'withdraw'])->name('courses.enrollments.withdraw');
        Route::post('enrollments/approve/bulk', [EnrollmentsController::class, 'bulkApprove'])
            ->middleware('role:Superadmin|Admin|Instructor')
            ->name('enrollments.bulk-approve');
        Route::post('enrollments/decline/bulk', [EnrollmentsController::class, 'bulkDecline'])
            ->middleware('role:Superadmin|Admin|Instructor')
            ->name('enrollments.bulk-decline');
        Route::post('enrollments/remove/bulk', [EnrollmentsController::class, 'bulkRemove'])
            ->middleware('role:Superadmin|Admin|Instructor')
            ->name('enrollments.bulk-remove');
        Route::post('enrollments/{enrollment}/approve', [EnrollmentsController::class, 'approve'])
            ->whereNumber('enrollment')
            ->middleware('can:approve,enrollment')
            ->name('enrollments.approve');
        Route::post('enrollments/{enrollment}/decline', [EnrollmentsController::class, 'decline'])
            ->whereNumber('enrollment')
            ->middleware('can:decline,enrollment')
            ->name('enrollments.decline');
        Route::post('enrollments/{enrollment}/remove', [EnrollmentsController::class, 'remove'])
            ->whereNumber('enrollment')
            ->middleware('can:remove,enrollment')
            ->name('enrollments.remove');
        Route::post('me/enrollments/invitations/{enrollment}/accept', [EnrollmentsController::class, 'acceptInvitation'])
            ->whereNumber('enrollment')
            ->middleware('role:Student')
            ->name('me.enrollments.invitations.accept');
        Route::post('me/enrollments/invitations/{enrollment}/decline', [EnrollmentsController::class, 'declineInvitation'])
            ->whereNumber('enrollment')
            ->middleware('role:Student')
            ->name('me.enrollments.invitations.decline');
    });

    
    Route::middleware(['throttle:api'])->group(function () {
        Route::get('courses/{course:slug}/enrollment-status', [EnrollmentsController::class, 'status'])
            ->name('courses.enrollments.status')
            ->middleware('role:Student');
        Route::get('courses/{course:slug}/enrollments', [EnrollmentsController::class, 'indexByCourse'])
            ->middleware('can:viewByCourse,Modules\\Enrollments\\Models\\Enrollment,course')
            ->name('courses.enrollments.index');
        Route::get('courses/{course:slug}/enrollments/{enrollment}', [EnrollmentsController::class, 'showByCourse'])
            ->whereNumber('enrollment')
            ->name('courses.enrollments.show');

        Route::get('enrollments', [EnrollmentsController::class, 'index'])
            ->middleware('role:Superadmin|Admin|Student')
            ->name('enrollments.index');
        Route::get('enrollments/{enrollment}', [EnrollmentsController::class, 'show'])
            ->whereNumber('enrollment')
            ->middleware('role:Superadmin|Admin|Student')
            ->name('enrollments.show');
        Route::get('enrollments/{enrollment}/activities', [EnrollmentsController::class, 'activities'])
            ->whereNumber('enrollment')
            ->name('enrollments.activities');
        Route::get('me/enrollments/invitations', [EnrollmentsController::class, 'listInvitations'])
            ->middleware('role:Student')
            ->name('me.enrollments.invitations.list');
        Route::get('me/enrollments/invitations/{enrollment}', [EnrollmentsController::class, 'showInvitation'])
            ->whereNumber('enrollment')
            ->middleware('role:Student')
            ->name('me.enrollments.invitations.show');
    });

    
});
