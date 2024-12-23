<?php

use App\Http\Controllers\Admin\Banner\BannerController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'role:Admin'])->name('banner')
  ->prefix('banner')
  ->group(function () {
    Route::get('index', [BannerController::class, 'index']);
    Route::get('show/{id}', [BannerController::class, 'show']);
    Route::put('update/{id}', [BannerController::class, 'update']);
    Route::delete('delete/{id}', [BannerController::class, 'destroy']);
  });

