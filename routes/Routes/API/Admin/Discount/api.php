<?php
use App\Http\Controllers\Admin\Discount\DiscountController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'role:Admin'])->name('discount.')
->prefix('discount')
->group(function () {
	Route::get('index', [DiscountController::class, 'index']);
	Route::get('edit/{id}', [DiscountController::class, 'edit']);
	Route::post('store', [DiscountController::class, 'store']);
	Route::delete('destroy/{id}', [DiscountController::class, 'destroy']);
	Route::put('update/{id}', [DiscountController::class, 'update']);
});

