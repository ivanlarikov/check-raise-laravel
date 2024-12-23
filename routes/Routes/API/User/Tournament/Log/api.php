<?php

use App\Http\Controllers\User\Tournament\Log\LogController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'role:Room Manager'])->name('log.')
        ->prefix('log')
        ->group(function () {
                Route::get('index', [LogController::class, 'index']);
        });
