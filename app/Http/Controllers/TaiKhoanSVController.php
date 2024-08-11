<?php

namespace App\Http\Controllers;

use App\Models\DiemDanh;
use App\Models\LichHoc;
use App\Models\SinhVien;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class TaiKhoanSVController extends Controller
{
    //
    public function show($id)
    {
        return SinhVien::findOrFail($id);
    }
    public function index(Request $request)
    {

        $name = $request->nameOrID;
        $query = User::join('sinh_vien', 'users.ma_sv', 'sinh_vien.ma_sv')
            ->select('users.ma_sv', 'sinh_vien.ten_sv', 'sinh_vien.email', 'sinh_vien.ma_lop', 'sinh_vien.phai');


        if (!empty($name)) {
            $query->where('sinh_vien.ten_sv', 'like', '%' . $name . '%')->orWhere('sinh_vien.ma_sv', 'like', '%' . $name . '%');
        }

        $query->orderBy('sinh_vien.ma_lop')->orderByRaw("SUBSTRING_INDEX(sinh_vien.ten_sv, ' ', -1)");


        $account = $query->paginate(10);

        $account->getCollection()->transform(function ($item, $key) use ($account) {
            $item->id = $key + 1 + ($account->currentPage() - 1) * $account->perPage();
            return $item;
        });

        return response()->json($account);
    }
    public function edit($id)
    {
        $taikhoansv = User::join('sinh_vien', 'users.ma_sv', 'sinh_vien.ma_sv')
            ->select('users.ma_sv', 'sinh_vien.ten_sv', 'sinh_vien.ngay_sinh', 'sinh_vien.email', 'sinh_vien.phai', 'sinh_vien.sdt', 'sinh_vien.ma_lop', 'sinh_vien.dia_chi', 'sinh_vien.avatar')
            ->where('sinh_vien.ma_sv', $id)
            ->first();

        return response()->json($taikhoansv);
    }
    public function destroy($id)
    {


        $student = SinhVien::find($id);

        if (!$student) {
            return response()->json(['message' => 'Sinh viên không tồn tại'], 404);
        }
        DiemDanh::where('ma_sv', $student->ma_sv)->delete();
        LichHoc::where('ma_sv', $student->ma_sv)->delete();
        User::where('ma_sv', $student->ma_sv)->delete();
        $student->delete();

        return response()->json(['message' => 'Xóa sinh viên thành công'], 200);

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
            'ma_lop' => 'required|exists:lop,ma_lop',
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
            $taikhoan->password = Hash::make($request->password);
            $taikhoan->save();
        }
        return response()->json(['message' => 'Cập nhật thành công!'], 200);
    }
    public function store(Request $request)
    {
        $request->validate([
            'ma_sv' => [
                'required',
                'string',
                'size:10',
                'unique:sinh_vien',
                'regex:/^(CD|DH)\d{8}$/',
            ],
            'ten_sv' => 'required|string|max:150|regex:/^[\p{L}\s\.\']+$/u',
            'ngay_sinh' => [
                'required',
                'date',
                function ($attribute, $value, $fail) {
                    try {
                        $birthDate = Carbon::parse($value);
                        $currentYear = Carbon::now()->year;
                        $birthYear = $birthDate->year;

                        if (($currentYear - $birthYear) < 18) {
                            $fail('Năm sinh không hợp lệ.');
                        }
                    } catch (\Exception $e) {
                        $fail('Không phải ngày hợp lệ');
                    }
                },
            ],
            'phai' => 'required|in:1,0',
            'dia_chi' => 'required|string|max:300',
            'sdt' => 'required|string|max:11|regex:/^[0-9]+$/',
            'email' => 'required|email|max:50|unique:sinh_vien',
            'password' => 'required|confirmed|min:8',
            'ma_lop' => 'required|exists:lop,ma_lop',
        ], [
            'ma_sv.required' => 'Nhập mã sinh viên',
            'ma_sv.string' => 'Mã sinh viên phải là chuỗi',
            'ma_sv.unique' => 'Mã sinh viên đã tồn tại',
            'ma_sv.regex' => 'Mã sinh viên không hợp lệ',
            'ma_sv.size' => 'Mã sinh viên phải có 10 ký tự',

            'ten_sv.required' => 'Nhập tên sinh viên',
            'ten_sv.string' => 'Tên sinh viên phải là chuỗi',
            'ten_sv.max' => 'Tên sinh viên không được vượt quá 150 ký tự',
            'ten_sv.regex' => 'Tên sinh viên không hợp lệ',

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
            'sdt.regex' => "số điện thoại phải là số",

            'email.required' => 'Nhập email',
            'email.email' => 'Email không hợp lệ',
            'email.max' => 'Email không được vượt quá 50 ký tự',
            'email.unique' => 'Email đã tồn tại',

            'password.required' => 'Nhập mật khẩu',
            'password.min' => 'Mật khẩu phải có 8 ký tự',
            'password.confirmed' => 'Mật khẩu xác nhận không trùng khớp',

            'ma_lop.required' => 'Chọn mã lớp',
            'ma_lop.exists' => 'Mã lớp không hợp lệ',
        ]);
        try {
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
                'anh_qr' => $request['ma_sv'] . 'png',
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
        } catch (Exception $ex) {
            return response()->json([
                'error' => $ex->getMessage(),
            ], 500);
        }
    }
}
