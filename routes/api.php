<?php

use App\Http\Controllers\LichDayController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PDTController;
use App\Http\Controllers\QLController;
use App\Http\Controllers\TaiKhoanGVController;
use App\Http\Controllers\UserController;

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/




Route::post('/login', [LoginController::class, 'login'])->name('login');
Route::middleware('auth:sanctum')->group(function () {
    // GiaoVien
    Route::get('/taikhoangv', [LoginController::class, 'getUserByToken']); //
    Route::post('/logout', [TaiKhoanGVController::class, 'logout']); //
    Route::get('/taikhoangvs', [TaiKhoanGVController::class, 'index']); //
    Route::get('/taikhoangv/{id}/edit', [TaiKhoanGVController::class, 'edit']); //
    Route::put('/taikhoangv/{id}', [TaiKhoanGVController::class, 'update']);
    Route::delete('/taikhoangv/{id}', [TaiKhoanGVController::class, 'destroy']);
    Route::post('/taikhoangv', [TaiKhoanGVController::class, 'store']);
    Route::get('/hocky/{id}', [LichDayController::class, 'getHocKy']);

    //SinhVien
    Route::get('/information/{id}', [UserController::class, 'show']);


    //PĐT
    Route::get('/tai-khoan-gv', [PDTController::class, 'getAllTeachers']); //
});
