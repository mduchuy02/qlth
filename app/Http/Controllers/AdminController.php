<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\GiaoVien;
use App\Models\SinhVien;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    public function getProfile()
    {
        try {
            $profile = Auth::user()->username;
            if (!$profile) {
                return response()->json(['error' => 'Unauthenticated.'], 401);
            }
            $admin = Admin::where('username', $profile)->select("username", "email", "full_name", "role","avatar")->firstOrFail();
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

    public function uploadAvatarAdmin(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        $ma_admin = Auth::user()->username;
        if (!$ma_admin) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }
        $admin = Admin::where('username', $ma_admin)->select("avatar")->firstOrFail();
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('avatars', $filename, 'public');

            // Optionally delete the old avatar file
            if ($admin->avatar) {
                Storage::disk('public')->delete($admin->avatar);
            }

            // Update the avatar path in the database
            $admin->avatar = $path;
            $admin->save();
            Storage::url($path);
            $update = Admin::where('username', $ma_admin)
                ->update([
                    'avatar' => $path
                ]);
            if ($update) {
                return response()->json(['avatarUrl' => $path], 200);
            }
            // return response()->json(['avatarUrl' => Storage::url($path)]);
            return response()->json(['avatarUrl' => $path]);
        }
        return response()->json(['error' => 'File not uploaded'], 500);
    }
    public function uploadAvatarSv(Request $request, $ma_sv) {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        if (!$ma_sv) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        $sinhVien = SinhVien::findOrFail($ma_sv);
        // Handle the file upload
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('avatars', $filename, 'public');

            // Optionally delete the old avatar file
            if ($sinhVien->avatar) {
                Storage::disk('public')->delete($sinhVien->avatar);
            }

            // Update the avatar path in the database
            $sinhVien->avatar = $path;
            $sinhVien->save();
            Storage::url($path);
            // return response()->json(['avatarUrl' => Storage::url($path)]);
            return response()->json(['avatarUrl' => $path]);
        }
    }
    public function uploadAvatarGv(Request $request, $ma_gv) {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        if (!$ma_gv) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        $giaoVien = GiaoVien::findOrFail($ma_gv);
        // Handle the file upload
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('avatars', $filename, 'public');

            // Optionally delete the old avatar file
            if ($giaoVien->avatar) {
                Storage::disk('public')->delete($giaoVien->avatar);
            }

            // Update the avatar path in the database
            $giaoVien->avatar = $path;
            $giaoVien->save();
            Storage::url($path);
            // return response()->json(['avatarUrl' => Storage::url($path)]);
            return response()->json(['avatarUrl' => $path]);
        }
    }
}
