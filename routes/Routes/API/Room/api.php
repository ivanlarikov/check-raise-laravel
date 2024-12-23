<?php
use App\Http\Controllers\Room\RoomController;
use Illuminate\Support\Facades\Route;

Route::get('show/{id}', [RoomController::class, 'show'])
    ->name('show');

Route::get('index', [RoomController::class, 'index'])
    ->name('index');
    

/*Route::middleware('auth:sanctum')
    ->post('logout',[SessionController::class, 'logout']);*/
