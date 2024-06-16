<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GiaoVien;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\TaiKhoanGV;
use Illuminate\Http\RedirectResponse;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('ma_gv', 'mat_khau');

        $user = TaiKhoanGV::where('ma_gv', $credentials['ma_gv'])->first();

        if ($user && Hash::check($credentials['mat_khau'], $user->mat_khau)) {
            $token = $user->createToken('GiaoVien')->plainTextToken;
            return response()->json(['token' => $token], 200);
        } else {
            return response()->json(['error' => 'Thông tin đăng nhập không chính xác'], 401);
        }
    }
    public function getUserByToken(Request $request)
    {
        $user = Auth::user();
        $giaovien = GiaoVien::findOrFail($user->ma_gv);
        return response()->json(['giaovien' => $giaovien], 200);
    }

}
