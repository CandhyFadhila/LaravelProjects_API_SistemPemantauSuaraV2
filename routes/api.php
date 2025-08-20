<?php

use App\Http\Controllers\Dashboard\AktivitasController;
use App\Http\Controllers\Dashboard\DetailMapController;
use App\Http\Controllers\Dashboard\PenggunaController;
use App\Http\Controllers\Dashboard\SuaraKPUController;
use App\Http\Controllers\Dashboard\UpcomingTPSController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Publik\Auth\LoginController;
use App\Http\Controllers\Publik\Auth\ResetPasswordController;
use App\Http\Controllers\Publik\Auth\ForgotPasswordController;
use App\Http\Controllers\PublikRequestController;

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

Route::post('/login', [LoginController::class, 'login']);
Route::post('/reset-password', [ResetPasswordController::class, 'resetPassword']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/logout', [LoginController::class, 'logout'])->middleware('web');

    // ! Publik Request
    Route::prefix('pemantau-suara/publik-request')->group(function () {
        Route::get('/get-all-users', [PublikRequestController::class, 'getAllDataUser']);
        Route::get('/get-all-users-penggerak', [PublikRequestController::class, 'getAllUserbyPenggerak']);
        Route::get('/get-all-roles', [PublikRequestController::class, 'getAllDataRole']);
        Route::get('/get-all-kecamatan', [PublikRequestController::class, 'getAllDataKecamatan']);
        Route::get('/get-all-kelurahan', [PublikRequestController::class, 'getAllDataKelurahan']);
        Route::get('/get-all-kelurahan-users/{userId}', [PublikRequestController::class, 'getKelurahanUserId']);
        Route::get('/get-all-status-aktivitas', [PublikRequestController::class, 'getAllStatusAktivitas']);
        Route::get('/get-all-aktivitas', [PublikRequestController::class, 'getAllDataAktivitas']);
        Route::get('/get-all-status-aktivitas-rw', [PublikRequestController::class, 'getAllStatusAktivitasRW']);
        // Route::get('/get-all-suara-kpu', [PublikRequestController::class, 'getAllDataSuaraKPU']);
        Route::get('/get-all-tps-mendatang', [PublikRequestController::class, 'getAllDataUpcomingTPS']);
        Route::get('/get-users-profile/{userId}', [PublikRequestController::class, 'getProfileUser']);
        Route::post('/get-map-kelurahan', [PublikRequestController::class, 'getDataMapsKelurahan']);
    });

    // ! Dashboard
    Route::prefix('pemantau-suara/dashboard')->group(function () {
        // ! Akun
        Route::prefix('credentials')->group(function () {
            Route::get('/account-info', [LoginController::class, 'getInfoUserLogin']);
            Route::post('/activate-pengguna/{id}', [PenggunaController::class, 'toggleStatusUser']);
            Route::post('/reset-password-pengguna/{id}', [PenggunaController::class, 'resetPasswordPengguna']);
            Route::post('/update-password-pengguna', [PenggunaController::class, 'updatePasswordPengguna']);
        });

        // ! Pengguna
        Route::prefix('management')->group(function () {
            Route::post('/get-pengguna', [PenggunaController::class, 'index']);
            Route::get('/export-pengguna', [PenggunaController::class, 'exportPengguna']);
            Route::post('/import-pengguna', [PenggunaController::class, 'importPengguna']);
            Route::apiResource('/pengguna', PenggunaController::class);

            Route::post('/get-aktivitas', [AktivitasController::class, 'index']);
            Route::get('/export-aktivitas', [AktivitasController::class, 'exportAktivitas']);
            Route::post('/import-aktivitas', [AktivitasController::class, 'importAktivitas']);
            Route::apiResource('/aktivitas', AktivitasController::class);

            Route::apiResource('/tps-mendatang', UpcomingTPSController::class);
        });

        // ! Monitoring Suara
        Route::prefix('monitoring')->group(function () {
            Route::post('/potensi-suara', [DetailMapController::class, 'indexPotensiSuara']);
            Route::post('/suara-kpu', [DetailMapController::class, 'indexSuaraKPU']);
            Route::get('/suara-kpu-export', [SuaraKPUController::class, 'exportKPU']);
            Route::post('/suara-kpu-import', [SuaraKPUController::class, 'importKPU']);
        });
    });
});
