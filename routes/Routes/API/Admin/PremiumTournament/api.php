<?php

use App\Http\Controllers\Admin\PremiumTournament\PremiumTournamentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'role:Admin'])->name('premium-tournament')
  ->prefix('premium_tournaments')
  ->group(function () {
    Route::get('index', [PremiumTournamentController::class, 'index']);
    Route::get('show/{id}', [PremiumTournamentController::class, 'show']);
    Route::put('update/{id}', [PremiumTournamentController::class, 'update']);
    Route::delete('delete/{id}', [PremiumTournamentController::class, 'destroy']);
  });

