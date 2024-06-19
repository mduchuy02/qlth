<?php

namespace App\Http\Controllers;

use App\Models\SinhVien;
use Illuminate\Http\Request;
use App\Models\GiaoVien;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\TaiKhoanGV;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Laravel\Sanctum\PersonalAccessToken;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('username', 'password');
        $user = User::where('username', $credentials['username'])->first();

        if ($user && Hash::check($credentials['password'], $user->password)) {
            $token = $user->createToken('authToken', ['*'], now()->addMinutes(120))->plainTextToken;

            // kiểm tra sinh viên hay giáo viên
            $role = $user->role;
            return response()->json([
                'token' => $token,
                'role' => $role,
            ], 200);
        } else {
            return response()->json(['error' => 'Thông tin đăng nhập không chính xác'], 401);
        }
    }
    public function validateToken(Request $request)
    {
        $token = $request->token;
        if (!$token) {
            return response()->json(['valid' => false, 'message' => 'Token is required'], 400);
        }

        $accessToken = PersonalAccessToken::findToken($token);

        if ($accessToken) {
            return response()->json(['valid' => true], 200);
        }

        return response()->json(['valid' => false, 'message' => 'Token is invalid or expired'], 401);
    }
    public function getUserByToken(Request $request)
    {
        $user = Auth::user();
        if ($user->role == 'teacher') {
            $giaovien = GiaoVien::findOrFail($user->username);
            $mappedGiaoVien = [
                'ma' => $giaovien->ma_gv,
                'ten' => $giaovien->ten_gv,
                'ngay_sinh' => $giaovien->ngay_sinh,
                'phai' => $giaovien->phai,
                'dia_chi' => $giaovien->dia_chi,
                'sdt' => $giaovien->sdt,
                'email' => $giaovien->email,
            ];
            return response()->json(['giaovien' => $mappedGiaoVien], 200);
        } elseif ($user->role == 'student') {
            $sinhvien = SinhVien::findOrFail($user->username);
            $mappedSinhVien = [
                'ma' => $sinhvien->ma_sv,
                'ten' => $sinhvien->ten_sv,
                'ngay_sinh' => $sinhvien->ngay_sinh,
                'phai' => $sinhvien->phai,
                'dia_chi' => $sinhvien->dia_chi,
                'sdt' => $sinhvien->sdt,
                'email' => $sinhvien->email,
                'anh_qr' => $sinhvien->anh_qr,
                'ma_lop' => $sinhvien->ma_lop
            ];
            return response()->json(['sinhvien' => $mappedSinhVien], 200);
        }
    }
}
