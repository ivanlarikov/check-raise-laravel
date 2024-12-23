<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VerificationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

/**
 * @desc Login/Logout
 */
Route::name('session.')
    ->prefix('session')
    ->group(
        __DIR__ . '/Routes/API/User/Session/api.php'
    );

Route::name('user.')
    ->prefix('user')
    ->group(
        __DIR__ . '/Routes/API/User/Registration/api.php',
    )->group(
        __DIR__ . '/Routes/API/User/Profile/api.php',
    )->group(
        __DIR__ . '/Routes/API/User/Room/api.php',
    )->group(
        __DIR__ . '/Routes/API/User/Tournament/api.php',
    )->group(
        __DIR__ . '/Routes/API/User/Director/api.php',
    )->group(
        __DIR__ . '/Routes/API/User/MyPlayer/api.php',
    )->group(
        __DIR__ . '/Routes/API/User/Player/api.php',
    )->group(
        __DIR__ . '/Routes/API/User/Banner/api.php',
    )->group(
        __DIR__ . '/Routes/API/User/PremiumTournament/api.php',
    );

Route::name('room.')
    ->prefix('room')
    ->group(
        __DIR__ . '/Routes/API/Room/api.php'
    );

Route::name('common.')
    ->prefix('common')
    ->group(
        __DIR__ . '/Routes/API/Common/api.php'
    );

Route::name('tournament.')
    ->prefix('tournament')
    ->group(
        __DIR__ . '/Routes/API/Tournament/api.php'
    );

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/* admin routes */
Route::name('admin.')
    ->prefix('admin')
    ->group(
        __DIR__ . '/Routes/API/Admin/Room/api.php',
    )->group(
        __DIR__ . '/Routes/API/Admin/Notification/api.php',
    )->group(
        __DIR__ . '/Routes/API/Admin/Setting/api.php',
    )->group(
        __DIR__ . '/Routes/API/Admin/PageSetting/api.php',
    )->group(
        __DIR__ . '/Routes/API/Admin/Tournament/api.php',
    )->group(
        __DIR__ . '/Routes/API/Admin/Player/api.php',
    )->group(
        __DIR__ . '/Routes/API/Admin/User/api.php',
    )->group(
        __DIR__ . '/Routes/API/Admin/Credit/api.php',
    )->group(
        __DIR__ . '/Routes/API/Admin/Discount/api.php',
    )->group(
        __DIR__ . '/Routes/API/Admin/Transaction/api.php',
    )->group(
        __DIR__ . '/Routes/API/Admin/EmailLog/api.php',
    )->group(
        __DIR__ . '/Routes/API/Admin/Banner/api.php',
    )->group(
        __DIR__ . '/Routes/API/Admin/PremiumTournament/api.php',
    );

/* player routes */

Route::name('player.')
    ->prefix('player')
    ->group(
        __DIR__ . '/Routes/API/Player/Tournament/api.php',
    );
/* verification */

Route::get('email/verify/{id}', [VerificationController::class, 'verify'])->name('verification.verify');
Route::get('email/resend', [VerificationController::class, 'resend'])->name('verification.resend');
