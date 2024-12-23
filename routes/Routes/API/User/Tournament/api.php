<?php

use App\Http\Controllers\User\Tournament\TournamentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'role:Room Manager'])->name('tournament.')
  ->prefix('tournament')
  ->group(function () {
    Route::get('index', [TournamentController::class, 'index']);
    Route::get('show/{id}', [TournamentController::class, 'show']);
    Route::post('store', [TournamentController::class, 'store']);
    Route::put('update', [TournamentController::class, 'update']);
    Route::post('duplicate', [TournamentController::class, 'duplicate']);
    Route::delete('destroy/{id}', [TournamentController::class, 'destroy']);
    Route::delete('destroy_finished', [TournamentController::class, 'destroyFinished']);
    Route::post('updatestatus', [TournamentController::class, 'updatestatus']);
    Route::post('updatefreezestatus', [TournamentController::class, 'updatefreezestatus']);
    Route::get('updatetournamentstatus/{id}', [TournamentController::class, 'updatetournamentstatus']);
    Route::get('archivetournament/{id}', [TournamentController::class, 'archivetournament']);
    Route::post('sendemail', [TournamentController::class, 'sendEmail']);
    Route::get('exportcsv/{id}', [TournamentController::class, 'exportcsv']);
    //save_tournament
    Route::post('set_premium', [TournamentController::class, 'set_premium']);
    Route::get('load_template/{id}', [TournamentController::class, 'load_template']);
    Route::get('templates', [TournamentController::class, 'templates']);
    Route::post('templates', [TournamentController::class, 'createTemplate']);
    Route::put('templates/{id}', [TournamentController::class, 'updateTemplate']);
    Route::delete('templates/{id}', [TournamentController::class, 'deleteTemplates']);
    Route::get('getroom', [TournamentController::class, 'getRoom']);
    Route::get('getlaterbyroom', [TournamentController::class, 'getLaterByRoom']);
    Route::delete('latedestroybyroom/{id}', [TournamentController::class, 'destroyByRoom']);
    Route::get('getlatearrivalbyid/{id}', [TournamentController::class, 'getLateArrivalById']);
    Route::post('latepdatebyroom/{id}', [TournamentController::class, 'updateByRoom']);
    Route::get('cuurentanonymous/{slug}/{id}', [TournamentController::class, 'cuurentanonymous']);

    //save_tournament

  })->group(
    __DIR__ . '/Checkin/api.php',
  )->group(
    __DIR__ . '/Log/api.php',
  );
