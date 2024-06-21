<?php

namespace App\Http\Controllers;

use App\Models\DiemDanh;
use App\Models\LichDay;
use App\Models\SinhVien;
use App\Models\Tkb;
use Illuminate\Http\Request;

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
        $maGdList = $tkbRecords->pluck('ma_gd');
        $sinhVienList = SinhVien::whereHas('lichHocs', function ($query) use ($maGdList) {
            $query->whereIn('ma_gd', $maGdList);
        })->get();
        if($sinhVienList->isEmpty()) {
            return response()->json(['message' => 'Không tìm thấy danh sách sinh viên phù hợp.'], 404);
        }
        $sinhVienList = $sinhVienList->map(function ($sinhVien, $index) {
            $sinhVien->key = $index + 1; // Adding 1 to start keys from 1 instead of 0
            return $sinhVien;
        });

        return response()->json($sinhVienList);
    }
    public function diemDanhSinhVien(Request $request)
    {
        $students = $request->input('students', []);
        $currentDate = now()->format('Y-m-d');

        try {
            foreach ($students as $student) {
                if($student['co_mat']) {
                    $tkb = Tkb::where("ma_gd", $student["ma_gd"])
                        ->where("ngay_hoc", $student["ngay_diem_danh"])
                        ->select("ma_tkb")
                        ->first(); // Lấy ra đối tượng Tkb thay vì danh sách
                    if ($tkb) {
                        $existingRecord = DiemDanh::where('ngay_hoc', $student["ngay_diem_danh"])
                            ->where('ma_tkb', $tkb->ma_tkb)
                            ->where('ma_sv', $student['ma_sv'])
                            ->first();
                        if (!$existingRecord) {
                            DiemDanh::create([
                                "ma_tkb" => $tkb->ma_tkb,
                                "ma_sv" => $student["ma_sv"], // Cung cấp giá trị ma_sv
                                "ngay_hoc" => $student["ngay_diem_danh"],
                                "diem_danh1" => $currentDate,
                                "diem_danh2" => null,
                            ]);
                        } else {
                            // Nếu đã có bản ghi có diem_danh1 là null, thì cập nhật diem_danh2
                            $existingRecord->update([
                                "diem_danh2" => $currentDate,
                            ]);
                        }
                    } else {
                        // Xử lý khi không tìm thấy $tkb, ví dụ thông báo lỗi hoặc xử lý khác
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
