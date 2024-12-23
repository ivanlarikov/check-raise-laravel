<?php

use App\Http\Controllers\Player\Tournament\TournamentController;
use App\Http\Controllers\Player\Tournament\LateArrivalController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'role:Player'])->name('tournament.')
  ->prefix('tournament')
  ->group(function () {
    Route::post('index', [TournamentController::class, 'index']);
    Route::post('register', [TournamentController::class, 'register']);
    Route::post('deregister', [TournamentController::class, 'deregister']);
    Route::get('mytournament', [TournamentController::class, 'mytournament']);
    Route::post('deregisterfromwaiting', [TournamentController::class, 'deregisterfromwaiting']);
    Route::get('show/{id}', [TournamentController::class, 'show']);

    Route::post('lateupdate/{id}', [LateArrivalController::class, 'announce']);
    Route::delete('lateremove/{id}', [LateArrivalController::class, 'destroy']);
  });

