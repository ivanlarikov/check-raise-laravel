<?php
use App\Http\Controllers\User\PremiumTournament\PremiumTournamentController;
use Illuminate\Support\Facades\Route;

Route::name('premiumtournament.')
    ->prefix('premiumtournament')
    ->group(function () {
        Route::get('gettodaypremiumtournament', [PremiumTournamentController::class, 'getTodayPremiumTournament']);
    });
	
Route::middleware(['auth:sanctum', 'role:Room Manager'])->name('premiumtournament.')
	->prefix('premiumtournament')
	->group(function () {
		Route::get('index', [PremiumTournamentController::class, 'index']);
		Route::post('store', [PremiumTournamentController::class, 'store']);
		Route::delete('destroy/{id}', [PremiumTournamentController::class, 'destroy']);
		Route::get('edit/{id}', [PremiumTournamentController::class, 'edit']);
		Route::put('update/{id}', [PremiumTournamentController::class, 'update']);
		Route::get('getroomtournamets', [PremiumTournamentController::class, 'getroomtournamets']);
		Route::get('getcredit', [PremiumTournamentController::class, 'getCredit']);
		Route::get('getPremiumweekly', [PremiumTournamentController::class, 'getPremiumweekly']);
		
	});
