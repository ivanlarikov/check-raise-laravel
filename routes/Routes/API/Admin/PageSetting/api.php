<?php

use App\Http\Controllers\Admin\PageSetting\PageSettingController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'role:Admin'])->name('pagesetting.')
	->prefix('page_setting')
	->group(function () {
		Route::get('index', [PageSettingController::class, 'index']);
		Route::get('show/{key}', [PageSettingController::class, 'show']);
		Route::put('update', [PageSettingController::class, 'update']);
	});
