<?php

namespace App\Http\Controllers;

use App\Models\LichDay;
use Illuminate\Http\Request;

class LichDayController extends Controller
{
    public function show($id)
    {
        // return TaiKhoanGV::join('giao_vien', 'tai_khoan_gv.ma_gv', 'giao_vien.ma_gv')
        //     ->select('tai_khoan_gv.ma_gv', 'giao_vien.ten_gv as name', 'giao_vien.ngay_sinh', 'giao_vien.phai', 'giao_vien.dia_chi', 'giao_vien.sdt', 'giao_vien.email')
        //     ->findOrFail($id);
        return LichDay::where('ma_gv',$id)->get();
    }
    public function getHocKy($ma_gv)
    {
        $lichDays = LichDay::where('ma_gv', $ma_gv)->get();
        $thoiGianHoc = [];
        foreach($lichDays as $lichDay) {
            $thoiGian = explode('-', $lichDay->thoi_gian);
            $ngayBatDau = date_create_from_format('d/m/Y', trim($thoiGian[0]));
            $month = $ngayBatDau->format('m');
            switch ($month) {
                case 9:
                case 10:
                case 11:
                case 12:
                case 1:
                    $hoc_ky = 1;
                    break;
                case 2:
                case 3:
                case 4:
                case 5:
                case 6:
                    $hoc_ky = 2;
                    break;
                case 7:
                case 8:
                    $hoc_ky = 3;
                    break;
            }
            $year = $ngayBatDau->format('Y');
            $lichDay->hoc_ky = "Học kỳ " . $hoc_ky . " năm học " . $year . "-" . $year+1;
            array_push($thoiGianHoc, $lichDay->hoc_ky);
        }
        return $thoiGianHoc;
    }
}
