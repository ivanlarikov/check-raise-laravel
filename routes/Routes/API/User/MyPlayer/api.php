<?php
use App\Http\Controllers\User\MyPlayer\MyPlayerController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'role:Room Manager'])->name('myplayer.')
        ->prefix('myplayer')
        ->group(function () {
			Route::get('index', [MyPlayerController::class, 'index']);
			Route::get('count', [MyPlayerController::class, 'getTotalCount']);
			Route::post('store', [MyPlayerController::class, 'store']);
			Route::get('saveexcel/{id}', [MyPlayerController::class, 'saveexcel']);
			Route::get('updatesuspendstatus/{user_id}', [MyPlayerController::class, 'updateSuspendStatus']);
        });
