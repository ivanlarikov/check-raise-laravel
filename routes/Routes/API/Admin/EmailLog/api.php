<?php

use App\Http\Controllers\Admin\EmailLog\EmailLogController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'role:Admin'])->name('emaillog.')
    ->prefix('emaillog')
    ->group(function () {
        Route::get('index', [EmailLogController::class, 'index']);
        Route::get('show/{id}', [EmailLogController::class, 'show']);
        Route::get('setting', [EmailLogController::class, 'getEmailSetting']);
        Route::put('setting', [EmailLogController::class, 'updateEmailSetting']);
    });
