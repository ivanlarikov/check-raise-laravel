<?php
use App\Http\Controllers\Admin\Notification\NotificationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'role:Admin'])->name('notification.')
->prefix('notification')
->group(function () {
        Route::get('index', [NotificationController::class, 'index']);
        Route::get('show/{id}', [NotificationController::class, 'show']);
        Route::post('store', [NotificationController::class, 'store']);
        Route::put('update', [NotificationController::class, 'update']);
        //Route::delete('destroy/{id}', [NotificationController::class, 'destroy']);
});

