<?php

use Illuminate\Support\Facades\Route;
use Modules\Trash\Http\Controllers\TrashBinController;

Route::prefix('v1')
    ->middleware(['auth:api', 'role:Admin|Superadmin|Instructor'])
    ->name('trash-bins.')
    ->group(function () {
        Route::get('trash-bins', [TrashBinController::class, 'index'])->name('index');
        Route::get('trash-bins/source-types', [TrashBinController::class, 'sourceTypes'])->name('source-types');
        Route::get('master-data/trash-bin-source-types', [TrashBinController::class, 'masterSourceTypes'])->name('source-types.master-data');
        Route::patch('trash-bins/{trashBinId}', [TrashBinController::class, 'restore'])->name('restore');
        Route::delete('trash-bins/{trashBinId}', [TrashBinController::class, 'forceDelete'])->name('delete');
        Route::patch('trash-bins', [TrashBinController::class, 'restoreAll'])->name('restore-all');
        Route::delete('trash-bins', [TrashBinController::class, 'forceDeleteAll'])->name('delete-all');
        Route::patch('trash-bins/bulk/restore', [TrashBinController::class, 'bulkRestore'])->name('bulk-restore');
        Route::post('trash-bins/bulk', [TrashBinController::class, 'bulkForceDelete'])->name('bulk-delete');
    });
