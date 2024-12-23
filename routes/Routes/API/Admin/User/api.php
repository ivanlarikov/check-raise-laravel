<?php
use App\Http\Controllers\Admin\User\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'role:Admin'])->name('user.')
->prefix('user')
->group(function () {
        Route::get('index', [UserController::class, 'index']);
		Route::get('verified/{id}', [UserController::class, 'verified']);
		Route::get('show/{id}', [UserController::class, 'show']);
        Route::post('update/{id}', [UserController::class, 'update']);
		Route::post('updatestatus/{id}', [UserController::class, 'updatestatus']);
        Route::post('loginuser', [UserController::class, 'loginuser']);
		Route::delete('destroy/{id}', [UserController::class, 'destroy']);
});

