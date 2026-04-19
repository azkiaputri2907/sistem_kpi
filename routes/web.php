<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KunjunganController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| 1. BAGIAN PENGUNJUNG (Public)
|--------------------------------------------------------------------------
*/
Route::get('/', [KunjunganController::class, 'create'])->name('landing');
Route::post('/kunjungan', [KunjunganController::class, 'store'])->name('kunjungan.store');
Route::get('/status/{kunjungan}', [KunjunganController::class, 'cekStatus'])->name('kunjungan.status');
Route::get('/survei/{id}', [KunjunganController::class, 'formSurvey'])->name('survey.form');
Route::post('/survei/simpan', [KunjunganController::class, 'storeSurvey'])->name('survey.store');

/*
|--------------------------------------------------------------------------
| 2. BAGIAN AUTHENTICATION
|--------------------------------------------------------------------------
*/
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::middleware('guest')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

/*
|--------------------------------------------------------------------------
| 3. BAGIAN DASHBOARD (Wajib Login)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    /**
     * ROUTE UTAMA
     * Otomatis filter: Admin ke Antrean, Kajur/Kaprodi ke Analytics
     */
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    /**
     * HALAMAN UMUM (Semua Role)
     */
    Route::get('/dashboard/analytics', [DashboardController::class, 'analytics'])->name('dashboard.analytics');
    Route::get('/dashboard/ulasan', [DashboardController::class, 'ulasanLayanan'])->name('dashboard.ulasan');
    Route::get('/dashboard/laporan', [DashboardController::class, 'laporan'])->name('dashboard.laporan');

    /**
    /**
     * HALAMAN OPERASIONAL (Admin & Super Admin)
     */
    Route::get('/dashboard/manajemen-antrean', [DashboardController::class, 'manajemenAntrean'])->name('dashboard.antrean');
    Route::post('/dashboard/mulai-proses/{kunjungan}', [DashboardController::class, 'mulaiProses'])->name('kunjungan.mulaiProses');
    Route::post('/dashboard/tolak/{kunjungan}', [DashboardController::class, 'tolak'])->name('kunjungan.tolak');
    Route::post('/dashboard/selesai/{kunjungan}', [DashboardController::class, 'selesai'])->name('kunjungan.selesai');

    // ---> TAMBAHKAN BARIS INI: ROUTE TANGGAPAN PIMPINAN <---
    Route::post('/dashboard/antrean/{kunjungan}/tanggapan', [DashboardController::class, 'tanggapanPimpinan'])->name('kunjungan.tanggapan');

    Route::post('/dashboard/kirim-email', [DashboardController::class, 'kirimEmailPimpinan'])->name('kunjungan.kirim-email');    /**
     * --- BARU: SISTEM CONTROL PANEL (Hanya Super Admin) ---
     * Mengelola master_user dan master_keperluan
     */
    Route::get('/dashboard/control-panel', [DashboardController::class, 'controlPanel'])->name('dashboard.control_panel');

    // Aksi CRUD Keperluan
    Route::post('/dashboard/keperluan', [DashboardController::class, 'storeKeperluan'])->name('keperluan.store');
    Route::delete('/dashboard/keperluan/{id}', [DashboardController::class, 'destroyKeperluan'])->name('keperluan.destroy');

    // Aksi User (Optional: Tambah/Edit User)
    Route::post('/dashboard/users', [DashboardController::class, 'storeUser'])->name('users.store');
});
