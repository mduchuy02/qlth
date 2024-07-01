<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function getProfile()
    {
        try {
            $profile = Auth::user()->username;
            if (!$profile) {
                return response()->json(['error' => 'Unauthenticated.'], 401);
            }
            $admin = Admin::where('username', $profile)->select("username", "email", "full_name", "role")->firstOrFail();
            return response()->json($admin);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }

    public function store(Request $request)
    {
        $username = $request->username;
        $request->validate(
            [
                "email" => "required|email|unique:admin,email,$username,username",
                'password' => 'sometimes|nullable|string|min:8|confirmed'
            ],
            [
                "email.required" => "Nhập email",
                'email.email' => 'Email không hợp lệ',
                'email.max' => 'Email không được vượt quá 50 ký tự',
                'email.unique' => 'Email đã tồn tại',

                'password.confirmed' => 'Mật khẩu xác nhận không trùng khớp',
            ]
        );
        if ($request->filled('password')) {
            $update = Admin::where('username', $username)
                ->update([
                    'email' => $request->email,
                    'full_name' => $request->full_name,
                    'password' => Hash::make($request->password),
                ]);
            if ($update) {
                return response()->json(['message' => 'Cập nhật thông tin giáo viên thành công!'], 200);
            }
        } else {
            $update = Admin::where('username', $username)
                ->update([
                    'email' => $request->email,
                    'full_name' => $request->full_name
                ]);
            if ($update) {
                return response()->json(['message' => 'Cập nhật thông tin giáo viên thành công!'], 200);
            }
        }
    }
}
