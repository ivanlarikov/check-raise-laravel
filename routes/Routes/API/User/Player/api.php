<?php

use App\Http\Controllers\User\Player\PlayerController;
use App\Http\Controllers\User\Player\PlayerStatisticsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'role:Room Manager'])->name('player.')
        ->prefix('player')
        ->group(function () {
                Route::get('index', [PlayerController::class, 'index']);
                Route::post('store', [PlayerController::class, 'store']);
                /*Route::get('show/{id}', [TournamentController::class, 'show']);
                Route::post('store', [TournamentController::class, 'store']);
                Route::put('update', [TournamentController::class, 'update']);
                Route::delete('destroy/{id}', [TournamentController::class, 'destroy']);
                Route::post('updatestatus', [TournamentController::class, 'updatestatus']);
                //save_tournament
                Route::post('set_premium', [TournamentController::class, 'set_premium']);
                Route::post('save_template', [TournamentController::class, 'save_template']);
                Route::get('load_template/{id}', [TournamentController::class, 'load_template']);
                Route::get('templates', [TournamentController::class, 'templates']);*/
                //save_tournament
				
				Route::get('statistics/{id}', [PlayerStatisticsController::class, 'index']);
                
        })->group(
                __DIR__ . '/Checkin/api.php',
            )->group(
                __DIR__ . '/Log/api.php',
            );
