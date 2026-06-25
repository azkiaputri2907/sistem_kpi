<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PimpinanKonfirmasiController;
use App\Http\Controllers\ControlPanelController;



use App\Http\Middleware\CekSessionLogin;
Route::get('/tes', function () {
    return 'TES BERHASIL';
});

Route::get('/api/antrean-diproses', 'App\Http\Controllers\KunjunganController@getAntreanDiproses')->name('api.antrean.diproses');
/*
notif data masuk dari pengunjung ke adminnn
*/
Route::get('/dashboard/check-notifications', [App\Http\Controllers\DashboardController::class, 'checkNotifications'])
    ->name('dashboard.check-notif');
/*
|--------------------------------------------------------------------------
| 1. BAGIAN PENGUNJUNG (Public)
|--------------------------------------------------------------------------
*/
// Diseragamkan menggunakan array class syntax

Route::get('/cek-pengunjung/{identitas}', 'App\Http\Controllers\KunjunganController@cekPengunjung')->name('pengunjung.cek');


Route::get('/', 'App\Http\Controllers\KunjunganController@create')->name('landing');
Route::post('/kunjungan', 'App\Http\Controllers\KunjunganController@store')->name('kunjungan.store');

// {id} di sini akan otomatis ditangkap sebagai $nomor_kunjungan di KunjunganController
Route::get('/status/{id}', 'App\Http\Controllers\KunjunganController@cekStatus')->name('kunjungan.status');
Route::get('/survei/{id}', 'App\Http\Controllers\KunjunganController@formSurvey')->name('survey.form');
Route::post('/survei/simpan', 'App\Http\Controllers\KunjunganController@storeSurvey')->name('survey.store');

/*
|--------------------------------------------------------------------------
| 2. BAGIAN AUTHENTICATION
|--------------------------------------------------------------------------
*/
// Tanpa middleware 'guest', karena filter login dilakukan manual di Controller
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

/*
|--------------------------------------------------------------------------
| 3. BAGIAN DASHBOARD (Wajib Login)
|--------------------------------------------------------------------------
*/
// Menggunakan middleware session manual pengganti auth bawaan SQL
Route::middleware([CekSessionLogin::class])->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::post('/dashboard/antrean/kirim-massal', 'App\Http\Controllers\KunjunganController@kirimMassal')->name('kunjungan.kirim-massal');

    // Halaman Khusus Pimpinan
    Route::get('/dashboard/pimpinan/konfirmasi', [PimpinanKonfirmasiController::class, 'index'])->name('pimpinan.konfirmasi');
    Route::post('/dashboard/pimpinan/konfirmasi/{id}/tanggapan', [PimpinanKonfirmasiController::class, 'tanggapan'])->name('pimpinan.tanggapan');

    /**
     * ROUTE UTAMA
     */
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/cek-total', [DashboardController::class, 'cekTotal']);

    /**
     * HALAMAN UMUM (Semua Role)
     */
    Route::get('/dashboard/analytics', [DashboardController::class, 'analytics'])->name('dashboard.analytics');
    Route::get('/dashboard/ulasan', [DashboardController::class, 'ulasanLayanan'])->name('dashboard.ulasan');
    Route::get('/dashboard/laporan', [DashboardController::class, 'laporan'])->name('dashboard.laporan');

    Route::get('/laporan/kunjungan', [DashboardController::class, 'exportKunjungan'])
        ->name('laporan.kunjungan');

    Route::get('/laporan/pengunjung', [DashboardController::class, 'exportPengunjung'])
        ->name('laporan.pengunjung');

    Route::get('/laporan/kinerja', [DashboardController::class, 'exportKinerja'])
        ->name('laporan.kinerja');

    Route::get('/laporan/penolakan', [DashboardController::class, 'exportPenolakan'])
        ->name('laporan.penolakan');

    Route::get('/laporan/ulasan', [DashboardController::class, 'exportUlasan'])
        ->name('laporan.ulasan');

    /**
     * HALAMAN OPERASIONAL (Admin & Super Admin)
     */
    Route::get('/dashboard/manajemen-antrean', [DashboardController::class, 'manajemenAntrean'])->name('dashboard.antrean');

    Route::post(
        '/dashboard/upload-file/{id}',
        [DashboardController::class,'uploadFile']
    )->name('kunjungan.upload');

    // Binding parameter disesuaikan dengan isi Controller tanpa Model Binding
    Route::post('/dashboard/mulai-proses/{nomor_kunjungan}', [DashboardController::class, 'mulaiProses'])->name('kunjungan.mulaiProses');
    Route::post('/dashboard/tolak/{id}', [DashboardController::class, 'tolak'])->name('kunjungan.tolak');
    Route::post('/dashboard/antrean/{id}/selesai', [DashboardController::class, 'selesai'])->name('kunjungan.selesai');

    /**
     * SISTEM TANGGAPAN & EMAIL
     */
    Route::post('/dashboard/antrean/{id}/tanggapan', [DashboardController::class, 'tanggapanPimpinan'])->name('kunjungan.tanggapan');
    Route::post('/dashboard/kirim-email', [DashboardController::class, 'kirimEmailPimpinan'])->name('kunjungan.kirim-email');

    /**
     * --- CONTROL PANEL (Hanya Super Admin) ---
     */
    Route::get('/dashboard/control-panel', [DashboardController::class, 'controlPanel'])->name('dashboard.control_panel');
    Route::post('/dashboard/keperluan', [DashboardController::class, 'storeKeperluan'])->name('keperluan.store');
    Route::delete('/dashboard/keperluan/{id}', [DashboardController::class, 'destroyKeperluan'])->name('keperluan.destroy');
    Route::post('/dashboard/users', [DashboardController::class, 'storeUser'])->name('users.store');
    // Pastikan dibungkus di dalam middleware session login Anda jika ada
    Route::get('/control-panel', [DashboardController::class, 'controlPanel'])->name('control-panel');
    Route::post('/control-panel/user/store', [DashboardController::class, 'storeUser'])->name('control-panel.user.store');
Route::put('/dashboard/control-panel/user/update/{id}', [DashboardController::class, 'updateUser'])->name('control-panel.user.update');
    Route::delete('/control-panel/user/delete/{id}', [DashboardController::class, 'destroyUser'])->name('control-panel.user.destroy');
});
