<?php

namespace App\Http\Controllers;

use App\Models\DiemDanh;
use App\Models\LichDay;
use App\Models\QrCode;
use App\Models\SinhVien;
use App\Models\Tkb;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DiemDanhController extends Controller
{
    public function getLichDiemDanh($ma_gv)
    {
        $lich = LichDay::where('ma_gv', $ma_gv)
            ->join('mon_hoc', 'mon_hoc.ma_mh', 'lich_gd.ma_mh')
            ->select('ma_gd', 'nmh', 'lich_gd.ma_mh', 'mon_hoc.ten_mh', 'st_bd', 'st_bd', 'st_kt', 'hoc_ky')
            ->get()
            // ->unique('ma_mh')
            ->map(function ($item) {
                $hocKy = $item->hoc_ky;
                $formatHK = intdiv($hocKy, 100);
                $fmNam = $hocKy % 100;
                $namBD = 2000 + $fmNam;
                $namKT = $namBD + 1;
                $namhoc = "Học kỳ $formatHK năm học $namBD-$namKT";

                $item->hoc_ky = $formatHK;
                $item->nam_hoc = $namhoc;
                return $item;
            });
        return response()->json($lich);
    }
    public function getDanhSachSinhVien(Request $request)
    {
        $tkbRecords = Tkb::where('ma_gd', $request->ma_gd)
            ->where('ngay_hoc', $request->ngay_hoc)
            ->get();
        $tkb = Tkb::where('ma_gd', $request->ma_gd)
            ->where('ngay_hoc', $request->ngay_hoc)
            ->select("ma_tkb")
            ->first();
        $maGd = $tkbRecords->pluck('ma_gd');
        $sinhVienList = SinhVien::whereHas('lichHocs', function ($query) use ($maGd) {
            $query->whereIn('ma_gd', $maGd);
        })->get();
        if ($sinhVienList->isEmpty()) {
            return response()->json(['message' => 'Không tìm thấy danh sách sinh viên phù hợp.'], 404);
        }
        $sinhVienListNew = $sinhVienList->map(function ($sinhVien, $index) use ($tkb) {
            $diemdanh1 = DiemDanh::where('ma_sv', $sinhVien->ma_sv)
                ->where('ma_tkb', $tkb['ma_tkb'])
                ->select('diem_danh1')
                ->first();
            $diemdanh2 = DiemDanh::where('ma_sv', $sinhVien->ma_sv)
                ->where('ma_tkb', $tkb['ma_tkb'])
                ->select('diem_danh2')
                ->first();
                // if(is_null($diemdanh1->diem_danh1)) {
                //     dd($sinhVien->diemdanh1 = false);
                // }else {
                //     $sinhVien->diemdanh1 = true;
                // }
                // if(is_null($diemdanh2->diem_danh2)) {
                //     $sinhVien->diemdanh2 = false;
                // }else {
                // $sinhVien->diemdanh2 = true;
                // }
            $sinhVien->diemdanh1 = !is_null($diemdanh1?->diem_danh1);
            $sinhVien->diemdanh2 = !is_null($diemdanh2?->diem_danh2);
            $sinhVien->key = $index + 1; // Adding 1 to start keys from 1 instead of 0
            return $sinhVien;
        });
        return response()->json($sinhVienListNew);
    }

    public function getIdTKB(Request $request)
    {
        try {
            // Thực hiện truy vấn để lấy dữ liệu từ bảng Tkb
            $idTKB = Tkb::where('ma_gd', $request->ma_gd)
                ->where('ngay_hoc', $request->ngay_hoc)
                ->select('ma_tkb', 'ma_gd', 'ngay_hoc')
                ->first();
            if (!$idTKB) {
                return response()->json(['message' => 'Không tìm thấy dữ liệu'], 404);
            }
            return response()->json($idTKB);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Đã xảy ra lỗi trong quá trình truy vấn cơ sở dữ liệu'], 500);
        }
    }
    public function saveQr(Request $request)
    {
        try {
            // Thực hiện insert dữ liệu vào bảng qrcode
            $code = $request->code;
            $hyphenPos = strpos($code, '-');
            $endTime = substr($code, strpos($code, '-', $hyphenPos + 1) + 1);
            $ma_tkb = substr($code, 0, $hyphenPos);
            $fromatCarbon = Carbon::parse($endTime);
            $formattedEndTime = $fromatCarbon->format('Y-m-d H:i:s');

            // kiemr tra xem qr quả tkb đã tồn tại chưa
            QrCode::where('ma_tkb', $ma_tkb)->delete();

            $qrCode = new QrCode();
            $qrCode->ma_tkb = $ma_tkb;
            $qrCode->thoi_gian_kt = $formattedEndTime;
            $qrCode->save();
            return response()->json($formattedEndTime);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Đã xảy ra lỗi khi lưu QR code: ' . $e->getMessage()], 500);
        }
    }
    public function diemDanhSinhVien(Request $request)
    {
        $students = $request->input('students', []);
        $currentDate = now()->format('Y-m-d');

        try {
            foreach ($students as $student) {
                $tkb = Tkb::where("ma_gd", $student["ma_gd"])
                    ->where("ngay_hoc", $student["ngay_diem_danh"])
                    ->select("ma_tkb")
                    ->first(); // Lấy ra đối tượng Tkb thay vì danh sách
                if ($tkb) {
                    $existingRecord = DiemDanh::where('ngay_hoc', $student["ngay_diem_danh"])
                        ->where('ma_tkb', $tkb->ma_tkb)
                        ->where('ma_sv', $student['ma_sv'])
                        ->first();
                    if ($student['co_mat']) {
                        if (!$existingRecord) {
                            DiemDanh::create([
                                "ma_tkb" => $tkb->ma_tkb,
                                "ma_sv" => $student["ma_sv"], // Cung cấp giá trị ma_sv
                                "ngay_hoc" => $student["ngay_diem_danh"],
                                "diem_danh1" => $currentDate,
                                "diem_danh2" => null,
                                "ghi_chu" => $student["ghi_chu"] ? $student["ghi_chu"] : "",
                            ]);
                        } else {
                            // Nếu đã có bản ghi có diem_danh1 là null, thì cập nhật diem_danh2
                            $existingRecord->update([
                                "diem_danh2" => $currentDate,
                            ]);
                        }
                    } else if ($student['co_phep']) {
                        if (!$existingRecord) {
                            DiemDanh::create([
                                "ma_tkb" => $tkb->ma_tkb,
                                "ma_sv" => $student["ma_sv"], // Cung cấp giá trị ma_sv
                                "ngay_hoc" => $student["ngay_diem_danh"],
                                "ghi_chu" => $student["ghi_chu"] ? $student["ghi_chu"] : "",
                            ]);
                        } else {
                            // Nếu đã có bản ghi có diem_danh1 là null, thì cập nhật diem_danh2
                            $existingRecord->update([
                                "ghi_chu" => 'có phép'
                            ]);
                        }
                    } else if ($student['khong_phep']) {
                        if (!$existingRecord) {
                            DiemDanh::create([
                                "ma_tkb" => $tkb->ma_tkb,
                                "ma_sv" => $student["ma_sv"], // Cung cấp giá trị ma_sv
                                "ngay_hoc" => $student["ngay_diem_danh"],
                                "ghi_chu" => $student["ghi_chu"] ? $student["ghi_chu"] : "",
                            ]);
                        } else {
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // Xử lý ngoại lệ khi có lỗi trong quá trình xử lý
            return response()->json(['message' => 'Lỗi khi gửi danh sách điểm danh!', 'error' => $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Điểm danh thành công!'], 200);
    }
}
