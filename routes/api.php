<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\DiemDanhController;
use App\Http\Controllers\ForgetPasswordController;
use App\Http\Controllers\GetAPIQRController;
use App\Http\Controllers\GiaoVienController;
use App\Http\Controllers\KetQuaController;
use App\Http\Controllers\KQDiemDanhController;
use App\Http\Controllers\LichDayController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LopController;
use App\Http\Controllers\PDTController;
use App\Http\Controllers\PDTThongKeController;
use App\Http\Controllers\SinhVienController;
use App\Http\Controllers\TaiKhoanGVController;
use App\Http\Controllers\TaiKhoanSVController;
use App\Http\Controllers\TKBController;
use App\Http\Controllers\UserController;
use App\Models\GiaoVien;
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



Route::post('/validate-token', [LoginController::class, 'validateToken']);
Route::post('/loginAdmin', [LoginController::class, 'loginAdmin'])->name('loginAdmin');
Route::post('/login', [LoginController::class, 'login'])->name('login');
Route::post('/forget-password-user', [ForgetPasswordController::class, 'forgetPassword']);
Route::post('/reset-password-user', [ForgetPasswordController::class, 'resetPassword']);
Route::post('/forget-password-admin', [ForgetPasswordController::class, 'forgetPasswordAdmin']);
Route::post('/reset-password-admin', [ForgetPasswordController::class, 'resetPasswordAdmin']);


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
    //Diem danh
    Route::get('/getLichDiemDanh/{id}', [DiemDanhController::class, 'getLichDiemDanh']);
    Route::post('/getDanhSachSinhVien', [DiemDanhController::class, 'getDanhSachSinhVien']);
    Route::post('/getIdTKB', [DiemDanhController::class, 'getIdTKB']);
    Route::put('/saveQr', [DiemDanhController::class, 'saveQr']);
    Route::post('/diemDanhSinhVien', [DiemDanhController::class, 'diemDanhSinhVien']);
    Route::post('/quet-ma-sinh-vien', [DiemDanhController::class, 'quetMaSinhVien']);
    Route::get('/getDanhSachDiemDanh/{ma_gd}', [DiemDanhController::class, 'getDanhSachDiemDanh']);
    Route::get('/exportDiemDanh/{ma_gd}', [DiemDanhController::class, 'exportDiemDanh']);
    //else
    Route::get('/getusertoken', [LoginController::class, 'getUserByToken']);
    Route::post('/logout', [TaiKhoanGVController::class, 'logout']);
    Route::get('/hocky', [LichDayController::class, 'getHocKy']);
    Route::get('/lichgd/{ma_gv}', [LichDayController::class, 'getLichGD']);
    //test
    Route::get('/getUser/{ma_gv}', [UserController::class, 'show']);
    //PDT
    Route::get('/thong-tin-admin', [AdminController::class, 'getProfile']);
    Route::post('/editProfileAdmin', [AdminController::class, 'store']);

    //Giao Vien
    Route::get('/profileGiaoVien', [GiaoVienController::class, 'profile']);
    Route::get('/tkbGiaoVien', [LichDayController::class, 'getThoiKhoaBieu']);
    Route::post('/editProfileGiaoVien', [GiaoVienController::class, 'store']);
    Route::get('/getTKBWeek/{value}', [TKBController::class, 'getTKBWeek']);

    Route::post('/create-schedule', [GiaoVienController::class, 'createSchedule']);
    Route::get('/getSubject', [GiaoVienController::class, 'getSubject']);
    Route::get('/teaching-schedule/{ma_gv}', [GiaoVienController::class, 'teachingSchedule']);
    Route::get('/schedule/{ma_gd}', [GiaoVienController::class, 'getSchedule']);
    Route::get('/students/{ma_gd}', [GiaoVienController::class, 'getStudent']);
    Route::delete('/teaching-schedule/{ma_gd}', [GiaoVienController::class, 'deleteTeachingSchedule']);
    Route::post('/send-list-id', [GiaoVienController::class, 'getStudentDetails']);
    Route::post('/add-students-to-schedule', [GiaoVienController::class, 'addStudents']);
    Route::delete('/delele-student-from-schedule', [GiaoVienController::class, 'deleteStudent']);
    Route::post('/edit-schedule', [GiaoVienController::class, 'editSchedule']);
    Route::post('/save-schedule', [GiaoVienController::class, 'saveSchedule']);
    Route::post('/create-schedule-custom', [GiaoVienController::class, 'createScheduleCustom']);


    Route::get('/testExport', [GiaoVienController::class, 'export']);

    //Sinh Vien
    Route::get('/thoi-khoa-bieu/{hocKy}', [TKBController::class, 'getTimeTable']);
    Route::get('/mon-hoc-diem-danh', [TKBController::class, 'getMonHocDiemDanh']);
    Route::get('/ket-qua-diem-danh/{ma_gd}/{ma_sv}', [KQDiemDanhController::class, 'getAttendance']);
    Route::get('/thong-tin-ca-nhan', [SinhVienController::class, 'profile']);
    Route::post('/sinh-vien-diem-danh', [SinhVienController::class, 'createAttandance']);
    Route::post('/edit', [SinhVienController::class, 'store']);
    Route::post('/upload-avatar', [SinhVienController::class, 'uploadAvatar']);
    Route::get('/getDiem', [KetQuaController::class, 'getDiem']);

    //PDT
    Route::post('/upload-avatar-admin', [AdminController::class, 'uploadAvatarAdmin']);
    Route::post('/upload-avatar-sv/{id}', [AdminController::class, 'uploadAvatarSv']);
    Route::post('/upload-avatar-gv/{id}', [AdminController::class, 'uploadAvatarGv']);
    Route::get('/thong-tin-admin', [AdminController::class, 'getProfile']);
    Route::get('/get-department-class', [PDTController::class, 'getListDepartmentClass']);
    Route::get('/get-department-class/{id}', [PDTController::class, 'getClass']);
    Route::post('/get-list-student', [PDTController::class, 'getListStudent']);
    Route::post('/export-data-students', [PDTController::class, 'exportData']);
    Route::post('/export-data-diemdanhs', [PDTController::class, 'exportDataDiemDanh']);
    Route::post('/import-data', [PDTController::class, 'importData']);
    Route::get('/list-department', [PDTController::class, 'getListDepartment']);
    Route::get('/list-department/{ma_khoa}', [PDTController::class, 'getDepartment']);
    Route::put('/list-department/save/{ma_khoa}', [PDTController::class, 'saveDepartment']);
    Route::post('/create-department', [PDTController::class, 'createDepartment']);
    Route::delete('/delete-department/{ma_khoa}', [PDTController::class, 'deleteDepartment']);
    Route::get('/get-list-class', [PDTController::class, 'getListClass']);
    Route::post('/create-student', [PDTController::class, 'createStudent']);
    Route::get('/list-classroom', [PDTController::class, 'getListClassroom']);
    Route::put('/list-classroom/save/{ma_lop}', [PDTController::class, 'saveClassroom']);
    Route::get('/list-classroom/{ma_lop}', [PDTController::class, 'getClassroom']);
    Route::delete('/delete-classroom/{ma_lop}', [PDTController::class, 'deleteClassroom']);
    Route::post('/create-classroom', [PDTController::class, 'createClassroom']);
    Route::get('/class-list-student', [PDTController::class, 'classListStudent']);

    //PDT Thống kê
    Route::get('/diem-hoc-tap', [PDTThongKeController::class, 'diemHocTap']);
    Route::get('/so-luong-sinh-vien', [PDTThongKeController::class, 'soLuongSinhVien']);
});
