<?php

use App\Http\Controllers\User\Director\TournamentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'role:Director'])->name('director.')
        ->prefix('director')
        ->group(function () {
                Route::get('index', [TournamentController::class, 'index']);
                Route::post('store', [TournamentController::class, 'store']);
                Route::delete('destroy/{id}', [TournamentController::class, 'destroy']);
                Route::get('updatetournamentstatus/{id}', [TournamentController::class, 'updatetournamentstatus']);
                Route::get('show/{id}', [TournamentController::class, 'show']);
                Route::get('archivetournament/{id}', [TournamentController::class, 'archivetournament']);

                Route::put('update', [TournamentController::class, 'update']);

                Route::post('updatestatus', [TournamentController::class, 'updatestatus']);
                Route::post('sendemail', [TournamentController::class, 'sendEmail']);
                Route::get('exportcsv/{id}', [TournamentController::class, 'exportcsv']);
                Route::post('save_template', [TournamentController::class, 'save_template']);
                Route::get('load_template/{id}', [TournamentController::class, 'load_template']);
                Route::get('templates', [TournamentController::class, 'templates']);
                Route::get('getroom', [TournamentController::class, 'getRoom']);

                //save_tournament
                 /* Route::post('set_premium', [TournamentController::class, 'set_premium']);


               */
                //save_tournament

        })->group(
                __DIR__ . '/Checkin/api.php',
        )->group(
                __DIR__ . '/Log/api.php',
        );
