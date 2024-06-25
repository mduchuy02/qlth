<?php

namespace App\Http\Controllers;

use App\Models\LichDay;
use App\Models\QrCode;
use App\Models\SinhVien;
use App\Models\Tkb;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DiemDanhController extends Controller
{
    //
    // public function getLichDiemDanh($ma_gv)
    // {
    //     $lich = LichDay::where('ma_gv', $ma_gv)
    //         ->join('mon_hoc', 'mon_hoc.ma_mh', 'lich_gd.ma_mh')
    //         ->select('ma_gd', 'lich_gd.ma_mh', 'mon_hoc.ten_mh', 'st_bd', 'st_bd', 'st_kt')->get()
    //         ->map(function ($item) {
    //             // Extract semester and year from hoc_ky
    //             $hocKy = $item->hoc_ky;
    //             $semester = intdiv($hocKy, 100); // The last digit represents the semester
    //             $yearCode = $hocKy % 100; // The remaining part represents the year code
    //             $startYear = 2000 + $yearCode;
    //             $endYear = $startYear + 1;
    //             $formattedHocKy = "Học kỳ $semester năm học $startYear-$endYear";

    //             // Modify the item with the formatted hoc_ky
    //             $item->nam_hoc = $formattedHocKy;
    //             return $item;
    //         });

    //     // Chuyển đổi collection thành mảng và trả về dưới dạng JSON
    //     return response()->json($lich);
    // }
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
        return response()->json($sinhVienList);
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
        } catch (QueryExecuted $e) {
            return response()->json(['message' => 'Đã xảy ra lỗi trong quá trình truy vấn cơ sở dữ liệu'], 500);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Đã xảy ra lỗi: ' . $e->getMessage()], 500);
        }
    }
    public function saveQr(Request $request)
    {
        try {
            // Thực hiện insert dữ liệu vào bảng qrcode
            $code = $request->code;
            $hyphenPos  = strpos($code, '-');
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
}
