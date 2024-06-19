<?php

use App\Http\Controllers\LichDayController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LopController;
use App\Http\Controllers\TaiKhoanGVController;
use App\Http\Controllers\TaiKhoanSVController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


Route::post('/validate-token', [LoginController::class, 'validateToken']);
Route::post('/login', [LoginController::class, 'login'])->name('login');

Route::middleware('auth:sanctum')->group(function () {
    //Tai khoan giao vien
    Route::get('/taikhoangvs', [TaiKhoanGVController::class, 'index']);
    Route::get('/taikhoangv/{id}', [TaiKhoanGVController::class, 'show']);
    Route::get('/taikhoangv/{id}/edit', [TaiKhoanGVController::class, 'edit']);
    Route::put('/taikhoangv/{id}', [TaiKhoanGVController::class, 'update']);
    Route::delete('/taikhoangv/{id}', [TaiKhoanGVController::class, 'destroy']);
    Route::post('/taikhoangv', [TaiKhoanGVController::class, 'store']);
    //Tai khoan sinh vien
    Route::get('/taikhoansvs', [TaiKhoanSVController::class, 'index']);
    Route::delete('/taikhoansv/{id}', [TaiKhoanSVController::class, 'destroy']);
    Route::get('/taikhoansv/{id}/edit', [TaiKhoansVController::class, 'edit']);
    Route::put('/taikhoansv/{id}', [TaiKhoanSVController::class, 'update']);
    Route::post('/taikhoansv', [TaiKhoanSVController::class, 'store']);
    //Lop
    Route::get('/lop', [LopController::class, 'index']);
    //get token
    Route::get('/getusertoken', [LoginController::class, 'getUserByToken']);
    //else
    Route::post('/logout', [TaiKhoanGVController::class, 'logout']);
    Route::get('/hocky/{id}', [LichDayController::class, 'getHocKy']);
    Route::get('/lichgd/{ma_gv}', [LichDayController::class, 'getLichGD']);
});