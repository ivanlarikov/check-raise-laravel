<?php

use App\Http\Controllers\User\Session\SessionController;
use Illuminate\Support\Facades\Route;

Route::post('login', [SessionController::class, 'login'])
    ->name('login');

Route::middleware('auth:sanctum')
    ->post('logout', [SessionController::class, 'logout']);

Route::post('forgot-password', [SessionController::class, 'forgotPassword']);
Route::post('reset-password', [SessionController::class, 'resetPassword']);
Route::post('verify-email', [SessionController::class, 'verifyEmail']);
Route::post('resend-verification-email', [SessionController::class, 'resendVerificationEmail']);
