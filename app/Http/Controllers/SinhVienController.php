<?php

namespace App\Http\Controllers;

use App\Models\DiemDanh;
use App\Models\LichHoc;
use App\Models\QrCode;
use App\Models\SinhVien;
use App\Models\Tkb;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

use function PHPUnit\Framework\isNull;

class SinhVienController extends Controller
{

    // lay tong tin sinh vien
    public function profile()
    {
        try {
            $ma_sv = Auth::user()->username;
            if (!$ma_sv) {
                return response()->json(['error' => 'Unauthenticated.'], 401);
            }
            return response()->json(SinhVien::findOrFail($ma_sv));
        } catch (\Exception $e) {
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }

    // xu ly sinh vien diem danh
    public function createAttandance(Request $request)
    {
        // lấy yêu cầu từ request
        $thoi_gian_dd = $request->thoi_gian_dd;
        $code = $request->code;
        $ma_sv = $request->ma_sv;

        // xử lý chuỗi trả về
        $hyphenPos  = strpos($code, '-');
        $ma_tkb = substr($code, 0, $hyphenPos);
        $formattedStartScan = Carbon::parse($thoi_gian_dd)->format('Y-m-d H:i:s');
        $ngay_dd = Carbon::parse($thoi_gian_dd)->format('Y-m-d');
        $trang_thai = substr($code, $hyphenPos + 1, strpos($code, '-', $hyphenPos) - 1); // điểm danh lần 1 hoặc 2
        //Kiểm tra xem mã Qr có hợp lệ hay không
        if (!preg_match('/^\d+-\d{1}-\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}.\d{3}\+\d{2}:\d{2}$/', $code)) {
            return response()->json([
                'message' => 'Mã QR không hợp lệ vui lòng thử lại'
            ], 400);
        }
        // Kiểm tra xem ma_tkb có tồn tại trong bảng qrcode
        $qrCode = QrCode::where('ma_tkb', $ma_tkb)->first();
        if (!$qrCode) {
            return response()->json([
                'message' => 'Mã thời khóa biểu không tồn tại',
            ], 400);
        }

        // lấy ra ma_gd tương ứng với tkb
        $maGD = Tkb::where('ma_tkb', $ma_tkb)->pluck('ma_gd')->first();
        $ngay_hoc = Tkb::where('ma_tkb', $ma_tkb)->pluck('ngay_hoc')->first();
        // kiểm tra xem sinh viên có ma_gd đó không trong table lich_hoc
        $lich_hoc =
            LichHoc::where('ma_sv', $ma_sv)
            ->where('ma_gd', $maGD)
            ->exists();

        // Nếu tồn tại thì xử lý
        if ($lich_hoc) {
            $tgHetHan = Carbon::parse($qrCode->thoi_gian_kt);
            $tgDiemDanh = Carbon::parse($formattedStartScan);
            // Kiểm tra mã qr còn hạn không
            if ($tgDiemDanh->lessThan($tgHetHan)) {
                $checkTonTai = DiemDanh::where('ma_tkb', $ma_tkb)
                    ->where('ma_sv', $ma_sv)
                    ->where('ngay_hoc', $ngay_hoc)
                    ->first();
                // Nếu sinh viên tồn tại ngày điểm danh thì update
                if ($checkTonTai) {
                    if ($trang_thai == 2) {
                        if (is_null($checkTonTai->diem_danh2)) {
                            DiemDanh::where('ma_dd', $checkTonTai->ma_dd)
                                ->update([
                                    'diem_danh2' => $formattedStartScan,
                                ]);
                            return response()->json([
                                'message' =>  "Điểm danh thành công"
                            ], 200);
                        } else {
                            return response()->json([
                                'message' =>  "Sinh Viên đã điểm danh rồiii"
                            ], 200);
                        }
                    } else {
                        return response()->json([
                            'message' =>  "Sinh Viên đã điểm danh rồi"
                        ], 200);
                    };
                } else { // Nếu sinh viên chưa tồn tại ngày điểm danh thì insert
                    DiemDanh::create([
                        'ma_tkb' => $ma_tkb,
                        'ma_sv' => $ma_sv,
                        'ngay_hoc' => $ngay_hoc,
                        'diem_danh1' => $formattedStartScan,
                        'ghi_chu' => ""
                    ]);
                    return response()->json([
                        'message' => 'Điểm danh thành công'
                    ], 200);
                }
            } else {
                return response()->json(['message' => 'Thời gian điểm danh đã hết vui lòng liên hệ giáo viên'], 400);
            }
        } else {
            return response()->json([
                'message' => "Sinh viên không có trong danh sách vui lòng liên hệ giáo viên"
            ], 400);
        }
    }

    //Sửa thông tin sinh viên
    public function store(Request $request)
    {
        $ma_sv = $request->ma_sv;
        $validated = $request->validate(
            [
                "email" => "required|email|unique:sinh_vien,email,$ma_sv,ma_sv",
                "sdt" => "required|numeric|digits_between:10,11|unique:sinh_vien,sdt,$ma_sv,ma_sv",
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
        $update = SinhVien::where('ma_sv', $ma_sv)
            ->update([
                'email' => $request->email,
                'sdt' => $request->sdt
            ]);
        if ($request->filled('password')) {
            $taikhoansv = User::where('username', $ma_sv)
                ->update([
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                ]);
        } else {
            $taikhoansv = User::where('username', $ma_sv)
                ->update([
                    'email' => $request->email,
                ]);
        }
        if ($update && $taikhoansv) {
            return response()->json(['message' => 'Cập nhật thông tin sinh viên thành công!'], 200);
        }
        
    }
}
