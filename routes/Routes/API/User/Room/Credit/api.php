<?php

use App\Http\Controllers\User\Room\Credit\TransactionController;

Route::name('credit.')
        ->prefix('credit')
        ->group(function () {
            
            Route::name('transaction.')
            ->prefix('transaction')
            ->group(function () {
                Route::get('index', [TransactionController::class, 'index']);
                Route::post('store', [TransactionController::class, 'store']);
            });
            
        });
