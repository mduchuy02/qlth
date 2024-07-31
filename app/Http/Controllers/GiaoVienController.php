<?php

namespace App\Http\Controllers;

use App\Exports\GiaoVienExport;
use App\Models\GiaoVien;
use App\Models\LichDay;
use App\Models\TaiKhoanGV;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class GiaoVienController extends Controller
{
    public function profile()
    {
        try {
            $ma_gv = Auth::user()->username;
            if (!$ma_gv) {
                return response()->json(['error' => 'Unauthenticated.'], 401);
            }
            return response()->json(GiaoVien::findOrFail($ma_gv));
        } catch (\Exception $e) {
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }

    public function store(Request $request)
    {
        $ma_gv = $request->ma_gv;
        $request->validate(
            [
                "email" => "required|email|unique:giao_vien,email,$ma_gv,ma_gv",
                "sdt" => "required|numeric|digits_between:10,11,|unique:giao_vien,sdt,$ma_gv,ma_gv",
                'password' => 'sometimes|nullable|string|min:8|confirmed'
            ],
            [
                "email.required" => "Nhập email",
                'email.email' => 'Email không hợp lệ',
                'email.max' => 'Email không được vượt quá 50 ký tự',
                'email.unique' => 'Email đã tồn tại',

                "sdt.required" => "Nhập số điện thoại",
                "sdt.numeric" => "Số điện thoại chỉ chứa số",
                "sdt.digits_between" => "Số điện thoại không hợp lệ",
                "sdt.unique" => 'Số điện thoại đã tồn tại',

                'password.confirmed' => 'Mật khẩu xác nhận không trùng khớp',
            ]
        );
        $update = GiaoVien::where('ma_gv', $ma_gv)
            ->update([
                'email' => $request->email,
                'sdt' => $request->sdt
            ]);
        if ($request->filled('password')) {
            $taikhoangv = User::where('username', $ma_gv)
                ->update([
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                ]);
            if ($update && $taikhoangv) {
                return response()->json(['message' => 'Cập nhật thông tin giáo viên thành công!'], 200);
            }
        } else {
            $taikhoangv = User::where('username', $ma_gv)
                ->update([
                    'email' => $request->email
                ]);
            if ($update && $taikhoangv) {
                return response()->json(['message' => 'Cập nhật thông tin giáo viên thành công!'], 200);
            }
        }


    }

    public function export()
    {
        try {
            $ma_gv = Auth::user()->username;
            $ma_gd = LichDay::where('ma_gv', $ma_gv)->pluck('ma_gd');
            if (!$ma_gv) {
                return response()->json(['error' => 'Unauthenticated.'], 401);
            }
            return (new GiaoVienExport($ma_gd))->download('test.xlsx');
        } catch (\Exception $e) {
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }
}
