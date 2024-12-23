<?php
use App\Http\Controllers\Admin\Transaction\TransactionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'role:Admin'])->name('transaction.')
->prefix('transaction')
->group(function () {
	Route::get('index', [TransactionController::class, 'index']);
	Route::get('edit/{id}', [TransactionController::class, 'edit']);
	Route::put('update/{id}', [TransactionController::class, 'update']);
	Route::delete('destroy/{id}', [TransactionController::class, 'destroy']);
});

