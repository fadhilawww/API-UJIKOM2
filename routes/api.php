<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AlatController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\KategoriController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\PeminjamanController;
use App\Http\Controllers\API\PengembalianController;

// Public Routes (Tidak perlu token)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected Routes (Wajib membawa Bearer Token dari Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Hanya Admin
    Route::middleware('role.admin')->group(function () {
        Route::apiResource('kategori', KategoriController::class);
        Route::apiResource('alat', AlatController::class);
        Route::get('/katalog', [AlatController::class, 'katalog']); 
        Route::apiResource('users', UserController::class);
        Route::get('/peminjaman', [PeminjamanController::class, 'index']);
        Route::get('/peminjaman/{peminjaman}', [PeminjamanController::class, 'show']);
        Route::post('/peminjaman/{peminjaman}/approve',[PeminjamanController::class, 'approve']);
        Route::put('/peminjaman/{peminjaman}', [PeminjamanController::class, 'update']);
        Route::delete('/peminjaman/{peminjaman}', [PeminjamanController::class, 'destroy']);
        Route::get('/pengembalian', [PengembalianController::class, 'index']);
        Route::get('/pengembalian/{pengembalian}', [PengembalianController::class,'show']);
        Route::put('/pengembalian/{pengembalian}', [PengembalianController::class,'update']);
        Route::delete('/pengembalian/{pengembalian}', [PengembalianController::class, 'destroy']);
        Route::get('/log-aktivitas', [LogAktivitasController::class, 'index']);
        Route::get('/laporan-peminjaman', [LaporanController::class, 'index']);
    });
    Route::middleware('role.petugas')->group(function () {
        Route::post('/peminjaman/{peminjaman}/approve', [PeminjamanController::class, 'approve']);
        Route::post('/pengembalian', [PengembalianController::class, 'store']);
        Route::get('/laporan-peminjaman', [LaporanController::class, 'index']);
        // Route untuk hak akses petugas
    });
    Route::middleware('role.peminjam')->group(function () {
        // Route untuk hak akses peminjam
        Route::get('/katalog', [AlatController::class, 'katalog']);
        Route::post('/peminjaman', [PeminjamanController::class, 'store']);
        Route::get('/riwayat-pinjam', [PeminjamanController::class, 'riwayat']);
    });
});
