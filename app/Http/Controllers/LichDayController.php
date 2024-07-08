<?php

namespace App\Http\Controllers;

use App\Models\LichDay;
use App\Models\LichHoc;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Auth;

class LichDayController extends Controller
{
    public function show($id)
    {
        // return TaiKhoanGV::join('giao_vien', 'tai_khoan_gv.ma_gv', 'giao_vien.ma_gv')
        //     ->select('tai_khoan_gv.ma_gv', 'giao_vien.ten_gv as name', 'giao_vien.ngay_sinh', 'giao_vien.phai', 'giao_vien.dia_chi', 'giao_vien.sdt', 'giao_vien.email')
        //     ->findOrFail($id);
        return LichDay::where('ma_gv', $id)->get();
    }
    public function getHocKy()
    {
        $ma = Auth::user()->username;
        $role = Auth::user()->role;
        if($role == 'teacher') {
            $querys = LichDay::where('ma_gv', $ma)->get('hoc_ky');
        } elseif($role == 'student') {
            $querys = LichHoc::where('ma_sv', $ma)->join('lich_gd','lich_gd.ma_gd','lich_hoc.ma_gd')->get('hoc_ky');
        }
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
                'lich_gd.nmh as NMH'
            )
            ->get();
        return response()->json($lichGD);
    }
    public function getThoiKhoaBieu()
    {
        try {
            $ma_gv = Auth::user()->username;
            $lichDays = LichDay::join('mon_hoc', 'mon_hoc.ma_mh', 'lich_gd.ma_mh')
                ->where('ma_gv', $ma_gv)
                ->select('ma_gd', 'ten_mh', 'hoc_ky', 'nmh')
                ->get();
            $lichDays = $lichDays->map(function ($lichDay) {
                $hocKy = str_split($lichDay->hoc_ky);
                $lichDay->hoc_ky = "Học kỳ " . $hocKy[0] . " năm học 20" . $hocKy[1] . $hocKy[2] . "-20" . $hocKy[1] . $hocKy[2] + 1;
                return $lichDay;
            });
            return response()->json($lichDays);
        } catch (Exception $e) {
            // return response()->json(['error' => 'Something went wrong'], 500);
            return response($e);
        }
    }
}
