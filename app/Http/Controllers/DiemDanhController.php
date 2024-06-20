<?php

namespace App\Http\Controllers;

use App\Models\LichDay;
use App\Models\Tkb;
use Illuminate\Http\Request;

class DiemDanhController extends Controller
{
    //
    public function getLichDiemDanh($ma_gv)
    {
        $lich = LichDay::where('ma_gv', $ma_gv)
            ->join('mon_hoc', 'mon_hoc.ma_mh', 'lich_gd.ma_mh')
            ->select('ma_gd','lich_gd.ma_mh', 'mon_hoc.ten_mh', 'hoc_ky','st_bd')->get()
            ->map(function ($item) {
                // Extract semester and year from hoc_ky
                $hocKy = $item->hoc_ky;
                $semester = intdiv($hocKy, 100); // The last digit represents the semester
                $yearCode = $hocKy % 100; // The remaining part represents the year code
                $startYear = 2000 + $yearCode;
                $endYear = $startYear + 1;
                $formattedHocKy = "Học kỳ $semester năm học $startYear-$endYear";

                // Modify the item with the formatted hoc_ky
                $item->hoc_ky_text = $formattedHocKy;
                return $item;
            });

        // Chuyển đổi collection thành mảng và trả về dưới dạng JSON
        return response()->json($lich);
    }
    public function getDanhSachSinhVien(Request $request)
    {
        $magd = $request->ma_gd;
        $ngayDiemDanh = $request->ngay_diem_danh;
        // $danhSach = LichDay::join('tkb', 'tkb.ma_gd', 'lich_gd.ma_gd')
        //     ->where('lich_gd.ma_gd',$magd)
        //     ->where('ngay_hoc',$ngayDiemDanh)
        //     ->get();
        $danhSach = Tkb::join('lich_hoc', 'tkb.ma_gd', 'lich_hoc.ma_gd')
        ->where('lich_hoc.ma_gd',$magd)
        ->where('ngay_hoc', $ngayDiemDanh)
        ->join('sinh_vien','sinh_vien.ma_sv','lich_hoc.ma_sv')
        ->select('ma_tkb','ngay_hoc','sinh_vien.ma_sv','ten_sv','ma_lop')
                ->get();

        if ($danhSach->isEmpty()) {
            return response()->json(['message' => 'Không tìm thấy danh sách sinh viên phù hợp.'], 404);
        }

        $danhSach = $danhSach->map(function ($item, $key) {
            $item->id = $key + 1;
            return $item;
        });
        return response()->json($danhSach);
    }
}
