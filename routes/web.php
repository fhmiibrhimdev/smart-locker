<?php

use App\Livewire\Example\Example;
use App\Livewire\Profile\Profile;
use App\Livewire\MasterData\Loker;
use App\Livewire\MasterData\Lokasi;
use App\Livewire\Dashboard\Dashboard;
use Illuminate\Support\Facades\Route;
use App\Livewire\Dashboard\DashboardKurir;
use App\Livewire\Control\User as ControlUser;
use App\Http\Controllers\Api\PickupController;
use App\Livewire\MasterData\TransactionDetail;
use App\Http\Controllers\Auth\AuthenticatedSessionController;

Route::get('/', [AuthenticatedSessionController::class, 'create'])
    ->name('login');

Route::post('/', [AuthenticatedSessionController::class, 'store']);
Route::get('/pickup/{kode_paket}', TransactionDetail::class);

// API untuk ESP CAM dan pickup system
Route::prefix('pickup')->group(function () {
    Route::post('/verify-and-claim', [PickupController::class, 'verifyAndClaim'])
        ->name('api.pickup.verify-and-claim');

    Route::post('/verify-code', [PickupController::class, 'verifyCode'])
        ->name('api.pickup.verify-code');

    Route::get('/status', [PickupController::class, 'getStatus'])
        ->name('api.pickup.status');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/profile', Profile::class);
});

Route::group(['middleware' => ['auth', 'role:admin']], function () {
    Route::get('/lokasi', Lokasi::class);
    Route::get('/loker', Loker::class);
    // Route::get('/example', Example::class);
    // Route::get('/control-user', ControlUser::class);
});

Route::group(['middleware' => ['auth', 'role:kurir']], function () {
    Route::get('/kurir/dashboard', DashboardKurir::class)->name('dashboard-kurir');
});
Route::group(['middleware' => ['auth', 'role:user']], function () {});
require __DIR__ . '/auth.php';
