<?php

namespace App\Http\Controllers;

use App\Models\SinhVien;
use App\Models\TaiKhoanSV;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TaiKhoanSVController extends Controller
{
    //
    public function show($id)
    {
        return SinhVien::findOrFail($id);
    }
    public function index()
    {
        // Get paginated results with 10 items per page
        $taikhoansvQuery = User::join('sinh_vien', 'users.ma_sv', 'sinh_vien.ma_sv')
            ->select('users.ma_sv', 'sinh_vien.ten_sv', 'sinh_vien.email', 'sinh_vien.phai');

        $taikhoansv = $taikhoansvQuery->paginate(10);

        // Add custom id field to each item
        $taikhoansv->getCollection()->transform(function ($item, $key) use ($taikhoansv) {
            $item->id = $key + 1 + ($taikhoansv->currentPage() - 1) * $taikhoansv->perPage();
            return $item;
        });

        // Return paginated results as JSON
        return response()->json($taikhoansv);
    }
    public function edit($id)
    {
        $taikhoansv = User::join('sinh_vien', 'users.ma_sv', 'sinh_vien.ma_sv')
            ->select('users.ma_sv', 'sinh_vien.ten_sv', 'sinh_vien.ngay_sinh', 'sinh_vien.email', 'sinh_vien.phai', 'sinh_vien.sdt', 'sinh_vien.ma_lop', 'sinh_vien.dia_chi')
            ->where('sinh_vien.ma_sv', $id)
            ->first();

        return response()->json($taikhoansv);
    }
    public function destroy($id)
    {
        User::find($id)->delete();
        SinhVien::find($id)->delete();
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'ten_sv' => 'required|string|max:150',
            'ngay_sinh' => 'required|date',
            'phai' => 'required|in:1,0',
            'dia_chi' => 'required|string|max:300',
            'sdt' => 'required|string|max:11',
            'email' => 'required|email|max:50',
            'ma_lop' => 'required|exists:lop,ma_lop', // Ensure ma_lop exists in lop table
            'password' => 'sometimes|nullable|string|min:8|confirmed'
        ]);
        $taikhoan = User::where('username', $id)->firstOrFail();
        $sinhvien = SinhVien::where('ma_sv', $taikhoan->ma_sv)->firstOrFail();
        $sinhvien->ten_sv = $request->ten_sv;
        $sinhvien->ngay_sinh = $request->ngay_sinh;
        $sinhvien->phai = $request->phai;
        $sinhvien->dia_chi = $request->dia_chi;
        $sinhvien->sdt = $request->sdt;
        $sinhvien->email = $request->email;
        $sinhvien->ma_lop = $request->ma_lop;

        $sinhvien->save();

        if ($request->filled('password')) {
            $taikhoan->mat_khau = Hash::make($request->password);
            $taikhoan->save();
        }

        return response()->json(['message' => 'Cập nhật thành công!'], 200);
    }
    public function store(Request $request)
    {
        $request->validate([
            'ma_sv' => 'required|string|max:10|unique:sinh_vien',
            'ten_sv' => 'required|string|max:150',
            'ngay_sinh' => 'required|date',
            'phai' => 'required|in:1,0',
            'dia_chi' => 'required|string|max:300',
            'sdt' => 'required|string|max:11',
            'email' => 'required|email|max:50|unique:sinh_vien',
            'password' => 'required|confirmed',
            'ma_lop' => 'required|exists:lop,ma_lop', // Thêm validation cho ma_lop
        ], [
            'ma_sv.required' => 'Nhập mã sinh viên',
            'ma_sv.string' => 'Mã sinh viên phải là chuỗi',
            'ma_sv.max' => 'Mã sinh viên không được vượt quá 10 ký tự',
            'ma_sv.unique' => 'Mã sinh viên đã tồn tại',

            'ten_sv.required' => 'Nhập tên sinh viên',
            'ten_sv.string' => 'Tên sinh viên phải là chuỗi',
            'ten_sv.max' => 'Tên sinh viên không được vượt quá 150 ký tự',

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

            'ma_lop.required' => 'Chọn mã lớp',
            'ma_lop.exists' => 'Mã lớp không hợp lệ',
        ]);

        // Tạo mới sinh viên và lưu vào cơ sở dữ liệu
        $sinh_vien = SinhVien::create([
            'ma_sv' => $request['ma_sv'],
            'ten_sv' => $request['ten_sv'],
            'ngay_sinh' => $request['ngay_sinh'],
            'phai' => $request['phai'],
            'dia_chi' => $request['dia_chi'],
            'sdt' => $request['sdt'],
            'email' => $request['email'],
            'ma_lop' => $request['ma_lop'], // Thêm ma_lop vào để lưu vào cơ sở dữ liệu
            'anh_qr' => $request['ma_sv'].'png',
        ]);

        // Tạo mới tài khoản sinh viên và lưu vào cơ sở dữ liệu
        $tai_khoan_sv = User::create([
            'ma_sv' => $request['ma_sv'],
            'username' => $request['ma_sv'],
            'password' => Hash::make($request['password']),
            'email' => $request['email'],
            'role' => 'student'
        ]);
        return response()->json(['message' => 'Tạo tài khoản sinh viên thành công!'], 200);
    }
}
