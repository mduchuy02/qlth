<?php

namespace App\Http\Controllers;

use App\Models\DiemDanh;
use App\Models\LichDay;
use App\Models\LichHoc;
use App\Models\QrCode;
use App\Models\SinhVien;
use App\Models\Tkb;
use Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

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
        $sinhVienList = $sinhVienList->map(function ($sinhVien) {
            $sinhVien->name = last(explode(' ', $sinhVien->ten_sv));
            return $sinhVien;
        });
        $sinhVienList = $sinhVienList->sortBy('name')->values();
        $sinhVienListNew = $sinhVienList->map(function ($sinhVien, $index) use ($tkb) {
            $diemdanh1 = DiemDanh::where('ma_sv', $sinhVien->ma_sv)
                ->where('ma_tkb', $tkb['ma_tkb'])
                ->select('diem_danh1')
                ->first();
            $diemdanh2 = DiemDanh::where('ma_sv', $sinhVien->ma_sv)
                ->where('ma_tkb', $tkb['ma_tkb'])
                ->select('diem_danh2')
                ->first();
            $sinhVien->diemdanh1 = !is_null($diemdanh1?->diem_danh1);
            $sinhVien->diemdanh2 = !is_null($diemdanh2?->diem_danh2);
            $sinhVien->key = $index + 1;
            return $sinhVien;
        });
        $sinhVienListNew = $sinhVienListNew->sortBy(function ($sinhVien) {
            $names = explode(' ', $sinhVien->ten_sv);
            return end($names);
        })->sortBy('ma_lop')->values();
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
        $currentDate = now()->format('Y-m-d H:i:s');

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
                        // if (!$existingRecord) {
                        //     DiemDanh::create([
                        //         "ma_tkb" => $tkb->ma_tkb,
                        //         "ma_sv" => $student["ma_sv"], // Cung cấp giá trị ma_sv
                        //         "ngay_hoc" => $student["ngay_diem_danh"],
                        //         "ghi_chu" => $student["ghi_chu"] ? $student["ghi_chu"] : "",
                        //     ]);
                        // } else {
                        // }
                    }
                }
            }
        } catch (\Exception $e) {
            // Xử lý ngoại lệ khi có lỗi trong quá trình xử lý
            return response()->json(['message' => 'Lỗi khi gửi danh sách điểm danh!', 'error' => $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Điểm danh thành công!'], 200);
    }

    public function quetMaSinhVien(Request $request)
    {
        $code = $request->code;
        $attendace = $request->attendace;
        $attendaceTime = Carbon::parse($request->attendaceTime)->format('Y-m-d H:i:s');
        $ma_gd = $request->ma_gd;
        $day = Carbon::parse($request->day)->format('Y-m-d');
        $ma_sv = substr($code, 0, strpos($code, '-'));

        // kiểm tra qrcode đúng định dạng không
        if (!preg_match('/^DH\d{8}-[\p{L}\s]+-D\d{2}_TH\d{2}$/u', $code)) {
            return response()->json([
                'message' => "Mã QR không hợp lệ"
            ], 400);
        }

        $tkb = Tkb::where('ma_gd', $ma_gd)
            ->where('ngay_hoc', $day)
            ->first();

        // kiểm tra xem  sinh viên có lịch học không
        $lich_hoc = LichHoc::where('ma_sv', $ma_sv)
            ->where('ma_gd', $ma_gd)
            ->exists();
        if ($lich_hoc) {
            $diem_danh = DiemDanh::where('ma_tkb', $tkb->ma_tkb)
                ->where('ma_sv', $ma_sv)
                ->where('ngay_hoc', $day)
                ->first();
            // Kiểm tra sinh viên đã có bản điểm danh hay chưa
            if ($diem_danh) {
                if ($attendace == 1) {
                    if (is_null($diem_danh->diem_danh1)) {
                        DiemDanh::where('ma_dd', $diem_danh->ma_dd)
                            ->update([
                                'diem_danh1' => $attendaceTime
                            ]);
                        return response()->json([
                            'message' => "Điểm danh thành công"
                        ], 200);
                    } else {
                        return response()->json([
                            'message' => "Sinh Viên đã điểm danh"
                        ], 400);
                    }
                } else {
                    if (is_null($diem_danh->diem_danh2)) {
                        DiemDanh::where('ma_dd', $diem_danh->ma_dd)
                            ->update([
                                'diem_danh2' => $attendaceTime
                            ]);
                        return response()->json([
                            'message' => "Điểm danh thành công"
                        ], 200);
                    } else {
                        return response()->json([
                            'message' => "Sinh Viên đã điểm danh"
                        ], 400);
                    }
                }
            } else {
                if ($attendace == 1) {
                    DiemDanh::create([
                        'ma_tkb' => $tkb->ma_tkb,
                        'ma_sv' => $ma_sv,
                        'ngay_hoc' => $day,
                        'diem_danh1' => $attendaceTime,
                    ]);
                    return response()->json([
                        'message' => "Điểm danh thành công"
                    ], 200);
                } else {
                    DiemDanh::create([
                        'ma_tkb' => $tkb->ma_tkb,
                        'ma_sv' => $ma_sv,
                        'ngay_hoc' => $day,
                        'diem_danh2' => $attendaceTime,
                    ]);
                    return response()->json([
                        'message' => "Điểm danh thành công"
                    ], 200);
                }
            }
        } else {
            return response()->json([
                'message' => "Sinh Viên không có lịch học môn này"
            ], 400);
        }
    }

    public function getDanhSachDiemDanh($ma_gd)
    {
        try {
            $ma_gv = Auth::user()->username;

            $tkb = Tkb::where('ma_gd', $ma_gd)
                ->pluck('ma_tkb');

            $diemdanh = DiemDanh::whereIn('ma_tkb', $tkb)
                ->select('ma_dd', 'ma_sv')
                ->get();
            $sinhviens = LichHoc::where('ma_gd', $ma_gd)
                ->select('ma_sv')
                ->get();

            $sinhviens->map(function ($sinhvien) use ($tkb) {
                $sinhvien->sbh = $tkb->count();

                $sinhvien->sbdd = DiemDanh::whereIn('ma_tkb', $tkb)
                    ->where('ma_sv', $sinhvien->ma_sv)
                    ->count();

                $sinhvien->ten_sv = SinhVien::where('ma_sv', $sinhvien->ma_sv)
                    ->select('ten_sv')
                    ->first()->ten_sv;

                $sinhvien->sbv = $sinhvien->sbh - $sinhvien->sbdd;


                $sinhvien->ma_lop = SinhVien::where('ma_sv', $sinhvien->ma_sv)
                    ->select('ma_lop')
                    ->first()->ma_lop;


                $sinhvien->diemqt = $this->customRound($sinhvien->sbdd * (10 / $tkb->count()));
                return $sinhvien;
            });


            $sinhviens = $sinhviens
                ->sortBy(function ($sinhvien) {
                    $names = explode(' ', $sinhvien->ten_sv);
                    return end($names);
                })
                ->sortBy('ma_lop')
                ->values();
            return response()->json($sinhviens);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Đã xảy ra lỗi khi lấy danh sách sinh viên: ' . $e->getMessage()], 500);
        }
    }
    function customRound($number)
    {
        $intPart = floor($number);
        $decimalPart = $number - $intPart;

        if ($decimalPart < 0.25) {
            return $intPart + 0.0;
        } elseif ($decimalPart < 0.75) {
            return $intPart + 0.5;
        } else {
            return $intPart + 1.0;
        }
    }

    // public function exportDiemDanh($ma_gd)
    // {
    //     try {
    //         $sinhviens = $this->getDanhSachDiemDanh($ma_gd)->original;
    //         $ten_mh = LichDay::join('mon_hoc', 'mon_hoc.ma_mh', 'lich_gd.ma_mh')->where('ma_gd', $ma_gd)->select('ten_mh')->first();

    //         $nmh = LichDay::where('ma_gd', $ma_gd)
    //             ->select('nmh')->first();

    //         $pdf = Pdf::loadView('attendance', compact('sinhviens', 'ten_mh', 'nmh'));
    //         return $pdf->download('danh_sach_diem_danh.pdf');
    //     } catch (\Exception $e) {
    //         return response()->json(['message' => 'Đã xảy ra lỗi khi xuất PDF: ' . $e->getMessage()], 500);
    //     }
    // }
    public function exportDiemDanh($ma_gd)
    {
        try {
            $sinhviens = $this->getDanhSachDiemDanh($ma_gd)->original;
            $ten_mh = LichDay::join('mon_hoc', 'mon_hoc.ma_mh', 'lich_gd.ma_mh')->where('ma_gd', $ma_gd)->select('ten_mh')->first();

            $nmh = LichDay::where('ma_gd', $ma_gd)
                ->select('nmh')->first();

            // return response()->json($sinhviens);
            // $pdf = Pdf::loadView('attendance', compact('sinhviens', 'ten_mh', 'nmh'));
            // $data = $request->input('data');
            return Excel::download(new \App\Exports\DiemDanhExport($sinhviens), 'dsdd.xlsx');
            // return $pdf->download('danh_sach_diem_danh.pdf');
        } catch (\Exception $e) {
            return response()->json(['message' => 'Đã xảy ra lỗi khi xuất PDF: ' . $e->getMessage()], 500);
        }
    }
}
