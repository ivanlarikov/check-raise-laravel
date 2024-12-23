<?php

use App\Http\Controllers\User\Profile\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->name('profile.')
        ->prefix('profile')
        ->group(function () {
                Route::post('update', [ProfileController::class, 'updateProfile']);
                Route::get('show', [ProfileController::class, 'showProfile']);
        });
