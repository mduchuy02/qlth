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
        return LichDay::where('ma_gv', $id)->get();
    }
    public function getHocKy($ma_gv)
    {
        $querys = LichDay::where('ma_gv', $ma_gv)->get('hoc_ky');
        foreach ($querys as $query) {
            $hocky = str_split($query);
            $query->hoc_ky_text = "Học kỳ " . $hocky[10] . " năm học 20" . $hocky[11] . $hocky[12] . "-20" . $hocky[11] . $hocky[12] + 1;
        }
        return response()->json($querys);
    }
    public function getLichGD(Request $request, $magv)
    {
        $hocKy = $request->query('hoc_ky');
        $lichGD = LichDay::where('ma_gv', $magv)
            ->where('hoc_ky', $hocKy)
            ->join('mon_hoc', 'lich_gd.ma_mh', '=', 'mon_hoc.ma_mh')
            ->select(
                'lich_gd.ma_gd as key',
                'lich_gd.ma_mh as MaMH',
                'mon_hoc.ten_mh as TenMH',
                'mon_hoc.so_tiet as SoTiet',
                'lich_gd.st_bd as TietBD',
                'lich_gd.st_kt as ST',
                'lich_gd.phong_hoc as Phong',
                'lich_gd.ngay_bd as NgayBD',
                'lich_gd.ngay_kt as NgayKT',
            )
            ->get();
        return response()->json($lichGD);
    }
}
