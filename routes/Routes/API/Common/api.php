<?php

use App\Http\Controllers\Common\ContactController;
use App\Http\Controllers\Common\CommonController;
use App\Http\Controllers\Common\DashboardController;
use App\Http\Controllers\Common\PageSettingController;
use Illuminate\Support\Facades\Route;

Route::name('contact.')
    ->prefix('contact')
    ->group(function () {
        Route::post('store', [ContactController::class, 'store']);
        Route::get('show/{id}', [ContactController::class, 'show']);
        Route::middleware(['auth:sanctum', 'role:Admin'])
            ->group(function () {
                Route::get('index', [ContactController::class, 'index']);
                Route::get('show/{id}', [ContactController::class, 'show']);
                Route::put('update', [ContactController::class, 'update']);
                Route::delete('delete/{id}', [ContactController::class, 'destroy']);
            });
    });

Route::name('common.')
    ->prefix('common')
    ->group(function () {
        Route::get('zipcode', [CommonController::class, 'zipcode']);
        Route::get('popup', [CommonController::class, 'popup']);
        Route::get('credits', [CommonController::class, 'credits']);
		Route::get('pagesetting', [CommonController::class, 'pagesetting']);
		Route::get('settings', [CommonController::class, 'settings']);
		Route::get('getrooms', [CommonController::class, 'getrooms']);
    });
/*Route::name('dashboard.')
    ->prefix('dashboard')
    ->group(function () {
        Route::get('show/{id}', [DashboardController::class, 'show']);
        Route::get('index', [DashboardController::class, 'index']);
        Route::get('room/{id}', [DashboardController::class, 'roomshow']);        
    });*/
