<?php

use App\Http\Controllers\Admin\AppController;
use App\Http\Controllers\Admin\Apps\SmartKeuController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\ClassController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MemberCategoryController;
use App\Http\Controllers\Admin\MemberController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AbsensiController;
use App\Http\Controllers\Pegawai\AbsensiPegawaiController;
use App\Http\Controllers\Pegawai\DashboardController as PegawaiDashboardController;
use App\Http\Controllers\Pegawai\SettingController as PegawaiSettingController;
use Illuminate\Support\Facades\Route;

Route::get("/", function () {
    return redirect("admin/auth/login");
});

Route::get('/admin/auth/login', [AuthController::class, 'login'])->name('login');
Route::post('/admin/auth/login', [AuthController::class, 'doLogin'])->name('do-login');
Route::get('/admin/auth/logout', [AuthController::class, 'doLogout'])->name('do-logout');

Route::prefix('pegawai')->group(function () {
    Route::get('/auth/login', [AuthController::class, 'loginPegawai']);
    Route::post('/auth/login', [AuthController::class, 'doLogin']);
    Route::get('/auth/logout', [AuthController::class, 'doLogoutPegawai']);
});

// Example Check Access Type
Route::prefix('admin')->middleware(["auth", "access-type:2"])->group(function () {
    Route::get('/xx', [DashboardController::class, 'index']);
});

Route::prefix('admin')->middleware("auth")->group(function () {
    Route::get('/auth/reset-default-password', [AuthController::class, 'resetDefaultPassword']);
    Route::post('/auth/reset-default-password', [AuthController::class, 'doResetDefaultPassword']);

    Route::get('/', [DashboardController::class, 'index']);
    Route::get('/test', [DashboardController::class, 'test']);
    Route::get('/test2', [DashboardController::class, 'test2']);

    Route::prefix('app')->group(function () {
        Route::get('/', [AppController::class, 'index']);
    });

    Route::prefix('member')->group(function () {
        Route::get('/', [MemberController::class, 'index']);
        Route::get('/api/search', [MemberController::class, 'searchAPI']);
        Route::get('/add', [MemberController::class, 'add']);
        Route::post('/add', [MemberController::class, 'doCreate']);
        Route::get('/detail/{id}', [MemberController::class, 'detail']);
        Route::get('/update/{id}', [MemberController::class, 'update']);
        Route::post("/update/{id}", [MemberController::class, 'doUpdate']);
        Route::get('/delete/{id}', [MemberController::class, 'doDelete']);
    });
    Route::prefix('member-category')->group(function () {
        Route::get('/', [MemberCategoryController::class, 'index']);
        Route::get('/add', [MemberCategoryController::class, 'add']);
        Route::post('/add', [MemberCategoryController::class, 'doCreate']);
        Route::get('/detail/{id}', [MemberCategoryController::class, 'detail']);
        Route::get('/update/{id}', [MemberCategoryController::class, 'update']);
        Route::post("/update/{id}", [MemberCategoryController::class, 'doUpdate']);
        Route::get('/delete/{id}', [MemberCategoryController::class, 'doDelete']);
    });

    Route::prefix('classe')->group(function () {
        Route::get('/', [ClassController::class, 'index']);
        Route::get('/api/search', [ClassController::class, 'searchAPI']);
        Route::get('/add', [ClassController::class, 'add']);
        Route::post('/add', [ClassController::class, 'doCreate']);
        Route::get('/detail/{id}', [ClassController::class, 'detail']);
        Route::get('/update/{id}', [ClassController::class, 'update']);
        Route::post("/update/{id}", [ClassController::class, 'doUpdate']);
        Route::get('/delete/{id}', [ClassController::class, 'doDelete']);
    });

    Route::prefix('absensi')->group(function () {
        Route::get('/', [AbsensiController::class, 'index']);
        Route::get('/add', [AbsensiController::class, 'add']);
        Route::post('/add', [AbsensiController::class, 'doCreate']);
        Route::get('/detail/{id}', [AbsensiController::class, 'detail']);
        Route::get('/edit/{id}', [AbsensiController::class, 'edit']);      
        Route::post('/edit/{id}', [AbsensiController::class, 'doUpdate']);
        Route::get('/delete/{id}', [AbsensiController::class, 'doDelete']);
        Route::get('/registrasi-wajah', [AbsensiController::class, 'registrasiWajah']);
        Route::post('/registrasi-wajah', [AbsensiController::class, 'doCreateWajah']);
        Route::get('/scan-wajah', [AbsensiController::class, 'scanWajah']);
        Route::post('/scan-wajah', [AbsensiController::class, 'doScanWajah']);
        Route::get('/exportToExcel', [AbsensiController::class, 'exportToExcel']);
    });

    Route::prefix('smart-keuangan')->group(function () {
        Route::get('/', [SmartKeuController::class, 'index']);
        Route::get('/sf/detail/{id}', [SmartKeuController::class, 'sfDetail']);
    });

    Route::prefix('user')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::get('/add', [UserController::class, 'add']);
        Route::post('/add', [UserController::class, 'doCreate']);
        Route::get('/update/{id}', [UserController::class, 'update']);
        Route::post("/update/{id}", [UserController::class, 'doUpdate']);
        Route::get('/update/{id}', [UserController::class, 'update']);
        Route::get('/reset-password/{id}', [UserController::class, 'resetPassword']);
        Route::get('/delete/{id}', [UserController::class, 'doDelete']);
    });

    Route::prefix('setting')->group(function () {
        Route::get('/general', [SettingController::class, 'general']);
        Route::post('/general', [SettingController::class, 'doUpdateGeneral']);
        Route::get('/absensi', [SettingController::class, 'absensi']);         
        Route::post('/absensi', [SettingController::class, 'doUpdateAbsensi']);
        Route::get('/change-password', [SettingController::class, 'changePassword']);
        Route::post('/change-password', [SettingController::class, 'doChangePassword']);
    });
});

Route::prefix('pegawai')->middleware("auth")->group(function () {
    Route::get('/', [PegawaiDashboardController::class, 'index']);
    Route::get('/app', [PegawaiDashboardController::class, 'index']);

    Route::prefix('absensi')->group(function () {
        Route::get('/', [AbsensiPegawaiController::class, 'index']);
        Route::get('/detail/{id}', [AbsensiPegawaiController::class, 'detail']);
        Route::get('/exportToExcel', [AbsensiPegawaiController::class, 'exportToExcel']);
    });

    Route::prefix('setting')->group(function () {
        Route::get('/general', [PegawaiSettingController::class, 'general']);
        Route::post('/general', [PegawaiSettingController::class, 'doUpdateGeneral']);
        Route::get('/change-password', [PegawaiSettingController::class, 'changePassword']);
        Route::post('/change-password', [PegawaiSettingController::class, 'doChangePassword']);
    });
});
