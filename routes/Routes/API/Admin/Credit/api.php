<?php
use App\Http\Controllers\Admin\Credit\CreditController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'role:Admin'])->name('credit.')
->prefix('credit')
->group(function () {
	Route::get('index', [CreditController::class, 'index']);
	Route::put('update/{id}', [CreditController::class, 'update']);
});

