<?php

use App\Http\Controllers\LichDayController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\TaiKhoanGVController;
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


Route::post('/login', [LoginController::class, 'login'])->name('login');
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/taikhoangv', [LoginController::class, 'getUserByToken']);
    Route::post('/logout', [TaiKhoanGVController::class, 'logout']);
    Route::get('/taikhoangvs', [TaiKhoanGVController::class, 'index']);
    Route::get('/taikhoangv/{id}', [TaiKhoanGVController::class, 'show']);
    Route::get('/taikhoangv/{id}/edit', [TaiKhoanGVController::class, 'edit']);
    Route::put('/taikhoangv/{id}', [TaiKhoanGVController::class, 'update']);
    Route::delete('/taikhoangv/{id}', [TaiKhoanGVController::class, 'destroy']);
    Route::post('/taikhoangv', [TaiKhoanGVController::class, 'store']);
    Route::get('/hocky/{id}', [LichDayController::class, 'getHocKy']);
    Route::get('/lichgd/{ma_gv}', [LichDayController::class, 'getLichGD']);
});