<?php
use App\Http\Controllers\Admin\Tournament\TournamentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'role:Admin'])->name('tournament.')
->prefix('tournament')
->group(function () {
	Route::get('index', [TournamentController::class, 'index']);
	Route::get('show/{id}', [TournamentController::class, 'show']);
	Route::post('store', [TournamentController::class, 'store']);
	Route::put('update', [TournamentController::class, 'update']);
	Route::delete('destroy/{id}', [TournamentController::class, 'destroy']);
	Route::post('updatestatus', [TournamentController::class, 'updatestatus']);
	Route::get('updatetournamentstatus/{id}', [TournamentController::class, 'updatetournamentstatus']);
	Route::get('archivetournament/{id}', [TournamentController::class, 'archivetournament']);
	Route::post('sendemail', [TournamentController::class, 'sendemail']);
	Route::get('exportcsv/{id}', [TournamentController::class, 'exportcsv']);
	//save_tournament
	Route::post('set_premium', [TournamentController::class, 'set_premium']);
	Route::post('save_template', [TournamentController::class, 'save_template']);
	Route::get('load_template/{id}', [TournamentController::class, 'load_template']);
	Route::get('templates', [TournamentController::class, 'templates']);
	Route::get('getroom', [TournamentController::class, 'getRoom']);
	Route::get('getAllroom', [TournamentController::class, 'getAllRoom']);
	Route::get('gettemplates', [TournamentController::class, 'getTemplates']);
	Route::get('getstatisticsbyroomid/{id}', [TournamentController::class, 'getStatisticsByRoomId']);
	Route::get('gettournamentlistbyroomId/{id}', [TournamentController::class, 'getTournamentListByRoomId']);
	Route::get('getlatearrival', [TournamentController::class, 'getLateArrival']);
	Route::get('getlatearrivalbyid/{id}', [TournamentController::class, 'getLateArrivalById']);
	Route::delete('destroylatearrival/{id}', [TournamentController::class, 'destroyLateArrival']);
	Route::post('updatelatearrival/{id}', [TournamentController::class, 'updateLateArrival']);
	
});

