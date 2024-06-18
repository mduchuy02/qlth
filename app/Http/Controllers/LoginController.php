<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GiaoVien;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\TaiKhoanGV;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('username', 'password');
        $user = User::where('username', $credentials['username'])->first();

        if ($user && Hash::check($credentials['password'], $user->password)) {
            $token = $user->createToken('authToken')->plainTextToken;

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
    public function getUserByToken(Request $request)
    {
        $user = Auth::user();
        $giaovien = GiaoVien::findOrFail($user->username);
        return response()->json(['giaovien' => $giaovien], 200);
    }
}
