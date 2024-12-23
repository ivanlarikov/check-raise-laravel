<?php
use App\Http\Controllers\Admin\Player\PlayerController;
use App\Http\Controllers\User\Player\PlayerStatisticsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'role:Admin'])->name('player.')
->prefix('player')
->group(function () {
        Route::get('index', [PlayerController::class, 'index']);
		Route::post('store', [PlayerController::class, 'store']);
		Route::delete('destroy/{id}', [PlayerController::class, 'destroy']);
		Route::get('statistics/{id}', [PlayerStatisticsController::class, 'index']);
		Route::post('storebyrole/{id}', [PlayerController::class, 'storeByRole']);
		Route::get('saveexcel/{id}', [PlayerController::class, 'saveexcel']);
		Route::post('updatesuspendstatus/{userid}', [PlayerController::class, 'updateSuspendStatus']);
		Route::post('update_expiry', [PlayerController::class, 'updateExpiry']);
		Route::post('update_first_reg_date', [PlayerController::class, 'updateFirstRegDate']);
        /*Route::post('updatestatus/{id}', [UserController::class, 'updatestatus']);
        Route::get('verified/{id}', [UserController::class, 'verified']);
        Route::post('loginuser', [UserController::class, 'loginuser']);*/
});

