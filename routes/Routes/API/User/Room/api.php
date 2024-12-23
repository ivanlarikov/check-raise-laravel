<?php

use App\Http\Controllers\User\Room\RoomController;
use App\Http\Controllers\User\Room\RoomStatisticsController;
use App\Http\Controllers\User\Room\RoomSettingsController;
use App\Http\Controllers\User\Room\PlayerController;
use App\Http\Controllers\User\Room\DirectorController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'role:Room Manager'])->name('room.')
  ->prefix('room')
  ->group(function () {
    Route::get('index', [RoomController::class, 'index']);
    Route::get('show/{id}', [RoomController::class, 'show']);
    Route::post('store', [RoomController::class, 'store']);
    Route::put('update', [RoomController::class, 'update']);
    Route::put('update_status', [RoomController::class, 'updateStatus']);
    Route::delete('destroy/{id}', [RoomController::class, 'destroy']);

    Route::get('player', [PlayerController::class, 'index']);
    Route::get('director', [DirectorController::class, 'index']);

    Route::post('director/store', [DirectorController::class, 'store']);
    //password
    Route::post('director/update', [DirectorController::class, 'update']);

    Route::delete('director/destroy/{id}', [DirectorController::class, 'destroy']);
    Route::get('statistics', [RoomStatisticsController::class, 'index']);
    Route::get('tournamentList', [RoomStatisticsController::class, 'tournamentList']);

    Route::get('settings/index', [RoomSettingsController::class, 'index']);
    Route::post('settings/store', [RoomSettingsController::class, 'store']);
  })->group(
    __DIR__ . '/Credit/api.php'
  );
