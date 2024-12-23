<?php
use App\Http\Controllers\Admin\Setting\SettingController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'role:Admin'])->name('setting.')
->prefix('setting')
->group(function () {
	Route::get('index', [SettingController::class, 'index']);
  Route::put('update/banner_interval', [SettingController::class, 'updateBannerInterval']);
	Route::put('update/{id}', [SettingController::class, 'update']);
	Route::put('update/defaultbanner/{id}/{slug}', [SettingController::class, 'defaultBannerUpdate']);
	Route::put('update/bannerposition/{id}/{slug}', [SettingController::class, 'bannerPosition']);
	Route::put('update/rollingtime/{id}/{slug}', [SettingController::class, 'rollingSetting']);
});

