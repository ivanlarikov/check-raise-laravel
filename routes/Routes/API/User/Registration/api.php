<?php

use App\Http\Controllers\User\Registration\PlayerRegistrationController;
use App\Http\Controllers\User\Registration\ManagerRegistrationController;
use Illuminate\Support\Facades\Route;

Route::name('registration.')
    ->prefix('registration')
    ->group(function () {

        Route::name('player')
            ->prefix('player')
            ->group(function () {
                Route::post('create', [PlayerRegistrationController::class, 'store']);
            });

        Route::name('manager')
            ->prefix('manager')
            ->group(function () {
                //Route::post('room_manager', [PlayerRegistrationController::class, 'room_manager']);
                Route::post('create', [ManagerRegistrationController::class, 'store']);
            });
    });
