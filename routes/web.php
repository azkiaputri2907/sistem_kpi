<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PimpinanKonfirmasiController;
use App\Http\Controllers\ControlPanelController;
use App\Http\Controllers\KunjunganController;



use App\Http\Middleware\CekSessionLogin;


Route::get('/', function () {
    return 'OK';
});