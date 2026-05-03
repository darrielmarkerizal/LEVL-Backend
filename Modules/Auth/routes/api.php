<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\AuthApiController;
use Modules\Auth\Http\Controllers\BenchmarkController;
use Modules\Auth\Http\Controllers\PasswordResetController;
use Modules\Auth\Http\Controllers\ProfileAccountController;
use Modules\Auth\Http\Controllers\ProfileActivityController;
use Modules\Auth\Http\Controllers\ProfileController;
use Modules\Auth\Http\Controllers\ProfilePasswordController;
use Modules\Auth\Http\Controllers\PublicProfileController;
use Modules\Auth\Http\Controllers\UserBulkController;
use Modules\Auth\Http\Controllers\UserManagementController;

Route::prefix('v1')
    ->as('auth.')
    ->group(function () {
        
        Route::middleware(['throttle:auth'])->group(function () {
            Route::post('/auth/register', [AuthApiController::class, 'register'])->name('register');
            Route::post('/auth/login', [AuthApiController::class, 'login'])->name('login');
            Route::post('/auth/set-password', [AuthApiController::class, 'setPassword'])
                ->middleware('auth:api')
                ->name('auth.set-password');
            Route::get('/auth/google/redirect', [AuthApiController::class, 'googleRedirect'])->name(
                'google.redirect',
            );
            Route::get('/auth/google/callback', [AuthApiController::class, 'googleCallback'])->name(
                'google.callback',
            );

            Route::post('/auth/email/verify', [AuthApiController::class, 'verifyEmail'])->name(
                'email.verify',
            );
        });

        Route::post('/auth/refresh', [AuthApiController::class, 'refresh'])
            ->middleware([\Modules\Auth\Http\Middleware\AllowExpiredToken::class, 'throttle:auth'])
            ->name('refresh');

        Route::middleware(['auth:api', 'throttle:api', \Modules\Auth\Http\Middleware\RestrictDeletedUserAccess::class])->group(function () {
            Route::post('/auth/logout', [AuthApiController::class, 'logout'])->name('logout');
            Route::post('/auth/set-username', [AuthApiController::class, 'setUsername'])->name(
                'set.username',
            );
            Route::post('/auth/email/verify/send', [
                AuthApiController::class,
                'sendEmailVerification',
            ])->name('email.verify.send');
            Route::post('/profile/email/change', [ProfileController::class, 'requestEmailChange'])->name(
                'email.change.request',
            );
            Route::post('/profile/email/change/verify', [ProfileController::class, 'verifyEmailChange'])->name(
                'email.change.verify',
            );

            
            Route::prefix('profile')
                ->as('profile.')
                ->group(function () {
                    Route::get('/', [ProfileController::class, 'index'])->name('index');
                    Route::put('/', [ProfileController::class, 'update'])->name('update');
                    Route::post('/avatar', [ProfileController::class, 'uploadAvatar'])->name('avatar.upload');
                    Route::delete('/avatar', [ProfileController::class, 'deleteAvatar'])->name(
                        'avatar.delete',
                    );

                    
                    Route::get('/activities', [ProfileActivityController::class, 'index'])->name(
                        'activities.index',
                    );

                    
                    Route::put('/password', [ProfilePasswordController::class, 'update'])->name(
                        'password.update',
                    );

                    
                    Route::post('/account/delete/request', [
                        ProfileAccountController::class,
                        'deleteRequest',
                    ])->name('account.delete.request');
                    Route::post('/account/delete/confirm', [
                        ProfileAccountController::class,
                        'deleteConfirm',
                    ])->name('account.delete.confirm');
                });

            
            Route::post('/profile/account/restore', [ProfileAccountController::class, 'restore'])
                ->name('profile.account.restore');

            Route::get('/users/{user}/profile', [PublicProfileController::class, 'show'])->name(
                'users.profile.show',
            );

            Route::get('/courses/{course:slug}/users/mentions', [\Modules\Auth\Http\Controllers\UserController::class, 'searchMentions'])->name(
                'users.mentions.search',
            );


            Route::middleware(['role:Admin,Superadmin'])->group(function () {
                Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
                Route::get('/users/{user}', [UserManagementController::class, 'show'])->name('users.show');
                Route::get('/users/{user}/enrolled-course', [UserManagementController::class, 'enrolledCourse'])->name('users.enrolled-course');
                Route::get('/users/{user}/assigned-schemes', [UserManagementController::class, 'assignedSchemes'])->name('users.assigned-schemes');
                Route::get('/user/{user}/latest-activity', [UserManagementController::class, 'latestActivity'])->name('users.latest-activity');

                Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
                Route::put('/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
                Route::put('/users/{user}/reset-password', [UserManagementController::class, 'resetPassword'])->name('users.reset-password');

                Route::post('/users/bulk/export', [UserBulkController::class, 'export'])->name('users.bulk.export');
                Route::post('/users/bulk/activate', [UserBulkController::class, 'activate'])->name('users.bulk.activate');
                Route::post('/users/bulk/deactivate', [UserBulkController::class, 'deactivate'])->name('users.bulk.deactivate');

                Route::middleware(['role:Superadmin'])->group(function () {
                    Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');
                    Route::delete('/users/bulk/delete', [UserBulkController::class, 'delete'])->name('users.bulk.delete');
                });
            });
        });

        
        Route::middleware(['throttle:auth'])->group(function () {
            Route::post('/auth/password/forgot', [PasswordResetController::class, 'forgot'])->name(
                'password.forgot',
            );
            Route::post('/auth/password/reset', [PasswordResetController::class, 'reset'])->name(
                'password.reset',
            );
            Route::post('/auth/password/forgot/confirm', [
                PasswordResetController::class,
                'confirmForgot',
            ])->name('password.forgot.confirm');
        });

        
        Route::middleware(['auth:api', 'throttle:api'])
            ->post('/auth/password/change', [PasswordResetController::class, 'changePassword'])
            ->name('password.change');

        
        Route::get('/dev/tokens', [AuthApiController::class, 'generateDevTokens'])
            ->name('dev.tokens');

        
        Route::prefix('benchmark')
            ->withoutMiddleware(['throttle:api', 'throttle:auth'])
            ->group(function () {
                Route::get('/users', [BenchmarkController::class, 'index'])->name('benchmark.users.index');
                Route::post('/users', [BenchmarkController::class, 'store'])->name('benchmark.users.store');
                Route::delete('/users', [BenchmarkController::class, 'destroy'])->name('benchmark.users.destroy');
            });
    });
