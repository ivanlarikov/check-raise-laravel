<?php

use App\Http\Controllers\Tournament\TournamentController;
use Illuminate\Support\Facades\Route;

Route::post('index', [TournamentController::class, 'index']);
Route::get('show/{id}', [TournamentController::class, 'show']);
Route::get('description/{id}', [TournamentController::class, 'getDescription']);
Route::get('table/{id}', [TournamentController::class, 'table']);
Route::get('getfilterdata', [TournamentController::class, 'getFilterData']);
