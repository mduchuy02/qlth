<?php

namespace App\Http\Controllers;

use App\Models\LichDay;
use App\Models\LichHoc;
use App\Models\Tkb;
use Exception;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class TKBController extends Controller
{
    public function getTimeTable($hocKy)
    {
        try {
            $ma_sv = Auth::user()->username;

            $attendances = LichHoc::join('lich_gd', 'lich_gd.ma_gd', 'lich_hoc.ma_gd')
                ->join('mon_hoc', 'lich_gd.ma_mh', '=', 'mon_hoc.ma_mh')
                ->join('giao_vien', 'giao_vien.ma_gv', 'lich_gd.ma_gv')
                ->where('ma_sv', $ma_sv)
                ->where('hoc_ky', $hocKy)
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
                    'lich_gd.nmh as NMH',
                    'giao_vien.ten_gv as TenGV'
                )
                ->get();

            return response()->json($attendances);
        } catch (Exception $e) {
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }

    public function getTKBWeek($value)
    {
        try {
            $ma = Auth::user()->username;
            $role = Auth::user()->role;

            $dayStart = substr($value, 0, 2);
            $monthStart = substr($value, 2, 2);
            $yearStart = substr($value, 4, 4);

            $dateStart = Carbon::createFromDate($yearStart, $monthStart, $dayStart)->startOfDay()->format('Y-m-d');
            $dateEnd = Carbon::createFromDate($yearStart, $monthStart, $dayStart)->addDays(6)->startOfDay()->format('Y-m-d');

            if ($role == 'teacher') {
                $tkbweek = Tkb::join('lich_gd', 'lich_gd.ma_gd', 'tkb.ma_gd')
                    ->join('mon_hoc', 'mon_hoc.ma_mh', 'lich_gd.ma_mh')
                    ->join('giao_vien', 'giao_vien.ma_gv', 'lich_gd.ma_gv')
                    ->where('giao_vien.ma_gv', $ma)
                    ->whereBetween('ngay_hoc', [$dateStart, $dateEnd])
                    ->select('mon_hoc.ma_mh', 'ten_mh', 'giao_vien.ma_gv', 'ten_gv', 'lich_gd.phong_hoc', 'ngay_hoc', 'lich_gd.st_bd', 'lich_gd.st_kt', 'ghi_chu', 'nmh')
                    ->get();
                $tkbweek->map(function ($item) {
                    $item->dayOfWeek = Carbon::parse($item->ngay_hoc)->format('l');
                    return $item;
                });
                return response()->json($tkbweek);
            } else if ($role == 'student') {
                $tkbweek = LichHoc::join('lich_gd', 'lich_gd.ma_gd', 'lich_hoc.ma_gd')
                    ->join('tkb', 'tkb.ma_gd', 'lich_hoc.ma_gd')
                    ->join('mon_hoc', 'mon_hoc.ma_mh', 'lich_gd.ma_mh')
                    ->join('giao_vien', 'giao_vien.ma_gv', 'lich_gd.ma_gv')
                    ->where('lich_hoc.ma_sv', $ma)
                    ->whereBetween('ngay_hoc', [$dateStart, $dateEnd])
                    ->select('mon_hoc.ma_mh', 'ten_mh', 'giao_vien.ma_gv', 'ten_gv', 'lich_gd.phong_hoc', 'ngay_hoc', 'lich_gd.st_bd', 'lich_gd.st_kt', 'ghi_chu', 'nmh')
                    ->get();
                $tkbweek->map(function ($item) {
                    $item->dayOfWeek = Carbon::parse($item->ngay_hoc)->format('l');
                    return $item;
                });
                return response()->json($tkbweek);
            }
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
