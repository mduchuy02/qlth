<?php

namespace App\Http\Controllers;

use App\Models\GiaoVien;
use App\Models\TaiKhoanGV;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


class TaiKhoanGVController extends Controller
{
    //

    public function index()
    {
        $taikhoangv = User::join('giao_vien', 'users.ma_gv', 'giao_vien.ma_gv')
            ->select('giao_vien.ma_gv', 'giao_vien.ten_gv as name', 'giao_vien.ngay_sinh', 'giao_vien.phai', 'giao_vien.dia_chi', 'giao_vien.sdt', 'giao_vien.email')
            ->get();

        $taikhoangv = $taikhoangv->map(function ($item, $key) {
            $item->id = $key + 1;
            return $item;
        });

        return response()->json($taikhoangv);
    }
    public function edit($id)
    {
        $taikhoangv = User::join('giao_vien', 'users.ma_gv', 'giao_vien.ma_gv')
            ->select('giao_vien.ma_gv', 'giao_vien.ten_gv as name', 'giao_vien.ngay_sinh', 'giao_vien.phai', 'giao_vien.dia_chi', 'giao_vien.sdt', 'giao_vien.email','giao_vien.avatar')
            ->where('giao_vien.ma_gv', $id)
            ->first();
        return response()->json($taikhoangv);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully'], 200);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'ten_gv' => 'required|string|max:150',
            'ngay_sinh' => 'required|date',
            'phai' => 'required|in:1,0',
            'dia_chi' => 'required|string|max:300',
            'sdt' => 'required|string|max:11',
            'email' => 'required|email|max:50',
            'password' => 'sometimes|nullable|string|min:8|confirmed'
        ]);

        $taikhoan = User::where('username', $id)->firstOrFail();
        $giaovien = GiaoVien::where('ma_gv', $taikhoan->username)->firstOrFail();

        $giaovien->ten_gv = $request->ten_gv;
        $giaovien->ngay_sinh = $request->ngay_sinh;
        $giaovien->phai = $request->phai;
        $giaovien->dia_chi = $request->dia_chi;
        $giaovien->sdt = $request->sdt;
        $giaovien->email = $request->email;
        $giaovien->save();

        if ($request->filled('password')) {
            $taikhoan->password = Hash::make($request->password);
            $taikhoan->save();
        }
        return response()->json(['message' => 'Cập nhật thành công!'], 200);
    }

    public function destroy($id)
    {
        TaiKhoanGV::find($id)->delete();
        GiaoVien::find($id)->delete();
    }

    public function store(Request $request)
    {
        $request->validate([
            'ma_gv' => 'required|string|max:10|unique:giao_vien',
            'ten_gv' => 'required|string|max:150',
            'ngay_sinh' => 'required|date',
            'phai' => 'required|in:1,0',
            'dia_chi' => 'required|string|max:300',
            'sdt' => 'required|string|max:11',
            'email' => 'required|email|max:50|unique:giao_vien',
            'password' => 'required|confirmed',
        ], [
            'ma_gv.required' => 'Nhập mã giáo viên',
            'ma_gv.string' => 'Mã giáo viên phải là chuỗi',
            'ma_gv.max' => 'Mã giáo viên không được vượt quá 10 ký tự',
            'ma_gv.unique' => 'Mã giáo viên đã tồn tại',

            'ten_gv.required' => 'Nhập tên giáo viên',
            'ten_gv.string' => 'Tên giáo viên phải là chuỗi',
            'ten_gv.max' => 'Tên giáo viên không được vượt quá 150 ký tự',

            'ngay_sinh.required' => 'Chọn ngày sinh',
            'ngay_sinh.date' => 'Ngày sinh phải có định dạng ngày',

            'phai.required' => 'Chọn giới tính',
            'phai.in' => 'Giới tính không hợp lệ',

            'dia_chi.required' => 'Nhập địa chỉ',
            'dia_chi.string' => 'Địa chỉ phải là chuỗi',
            'dia_chi.max' => 'Địa chỉ không được vượt quá 300 ký tự',

            'sdt.required' => 'Nhập số điện thoại',
            'sdt.string' => 'Số điện thoại phải là chuỗi',
            'sdt.max' => 'Số điện thoại không được vượt quá 11 ký tự',

            'email.required' => 'Nhập email',
            'email.email' => 'Email không hợp lệ',
            'email.max' => 'Email không được vượt quá 50 ký tự',
            'email.unique' => 'Email đã tồn tại',

            'password.required' => 'Nhập mật khẩu',
            'password.confirmed' => 'Mật khẩu xác nhận không trùng khớp',
        ]);

        // Tạo mới giáo viên và lưu vào cơ sở dữ liệu
        $giao_vien = GiaoVien::create([
            'ma_gv' => $request["ma_gv"],
            'ten_gv' => $request["ten_gv"],
            'ngay_sinh' => $request["ngay_sinh"],
            'phai' => $request["phai"],
            'dia_chi' => $request["dia_chi"],
            'sdt' => $request["sdt"],
            'email' => $request["email"],
        ]);

        // Tạo mới tài khoản giáo viên và lưu vào cơ sở dữ liệu
        $taikhoangv = User::create([
            'username' => $request["ma_gv"],
            'ma_gv' => $request["ma_gv"],
            'password' => Hash::make($request["password"]),
            'email' => $request["email"],
            'role' => 'teacher'
        ]);

        return response()->json(['message' => 'Tạo tài khoản giáo viên thành công!'], 200);
    }
}
