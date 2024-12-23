<?php

use App\Http\Controllers\User\Tournament\Checkin\CheckinController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'role:Room Manager'])->name('checkin.')
  ->prefix('checkin')
  ->group(function () {
    Route::get('index/{id}', [CheckinController::class, 'index']);
    Route::get('users', [CheckinController::class, 'users']);
    Route::post('register', [CheckinController::class, 'register']);
    Route::post('deregister', [CheckinController::class, 'deregister']);

    Route::post('checkin', [CheckinController::class, 'checkin']);
    Route::post('cancelcheckin', [CheckinController::class, 'cancelcheckin']);

    Route::post('plusrebuy', [CheckinController::class, 'plusrebuy']);
    Route::post('minusrebuy', [CheckinController::class, 'minusrebuy']);

    Route::post('updatecounts', [CheckinController::class, 'updateCounts']);

    Route::post('checkout', [CheckinController::class, 'checkout']);
  });
