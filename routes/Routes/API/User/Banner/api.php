<?php
use App\Http\Controllers\User\Banner\BannerController;
use Illuminate\Support\Facades\Route;

Route::name('banner.')
    ->prefix('banner')
    ->group(function () {
        Route::get('gettodaybanner/{location}', [BannerController::class, 'getTodayBanner']);
    });
	
Route::middleware(['auth:sanctum', 'role:Room Manager'])->name('banner.')
	->prefix('banner')
	->group(function () {
		Route::get('index', [BannerController::class, 'index']);
		Route::get('edit/{id}', [BannerController::class, 'edit']);
		Route::post('store', [BannerController::class, 'store']);
		Route::delete('destroy/{id}', [BannerController::class, 'destroy']);
		Route::put('update/{id}', [BannerController::class, 'update']);
		Route::get('getcredit', [BannerController::class, 'getCredit']);
		Route::get('getbannerweekly', [BannerController::class, 'getbannerweekly']);
	});
