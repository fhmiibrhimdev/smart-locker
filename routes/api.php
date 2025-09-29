<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PickupController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// API untuk ESP CAM dan pickup system (tanpa CSRF protection)
Route::prefix('pickup')->group(function () {
    Route::post('/verify-and-claim', [PickupController::class, 'verifyAndClaim'])
        ->name('api.pickup.verify-and-claim');

    Route::post('/verify-code', [PickupController::class, 'verifyCode'])
        ->name('api.pickup.verify-code');

    Route::get('/status', [PickupController::class, 'getStatus'])
        ->name('api.pickup.status');
});

// Health check endpoint untuk ESP CAM
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'version' => '1.0.0'
    ]);
});
