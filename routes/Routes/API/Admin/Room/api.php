<?php
use App\Http\Controllers\Admin\Room\RoomController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'role:Admin'])->name('room.')
->prefix('room')
->group(function () {
        Route::get('index', [RoomController::class, 'index']);
        Route::get('show/{id}', [RoomController::class, 'show']);
        Route::post('store', [RoomController::class, 'store']);
        Route::put('update', [RoomController::class, 'update']);
        Route::post('status', [RoomController::class, 'status']);
        Route::delete('destroy/{id}', [RoomController::class, 'destroy']);
        Route::post('sendemail/{id}', [RoomController::class, 'sendemail']);
});

