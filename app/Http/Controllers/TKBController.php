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
                    ->select('mon_hoc.ma_mh', 'ten_mh', 'giao_vien.ma_gv', 'ten_gv', 'lich_gd.phong_hoc', 'ngay_hoc', 'tkb.st_bd', 'tkb.st_kt', 'ghi_chu', 'nmh')
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
                    ->select('mon_hoc.ma_mh', 'ten_mh', 'giao_vien.ma_gv', 'ten_gv', 'lich_gd.phong_hoc', 'ngay_hoc', 'tkb.st_bd', 'tkb.st_kt', 'ghi_chu', 'nmh')
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
    public function getMonHocDiemDanh()
    {
        try {
            $ma_sv = Auth::user()->username;

            $attendances = LichHoc::where('ma_sv', $ma_sv)
                ->with(['lichGD.giaoVien', 'lichGD.monHoc'])
                ->get()
                ->map(function ($item) {
                    return $item->lichGD;
                });
            return response()->json($attendances);
        } catch (Exception $e) {
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }

    public function createSchedule(Request $request)
    {
        $request->validate(
            [
                'ma_gv' => 'required|string|max:10',
                'ma_mh' => 'required|string|max:20',
                'nmh' => 'required|integer',
                'phong_hoc' => 'required|string|max:10',
                'ngay_bd' => 'required|date',
                'ngay_kt' => 'required|date',
                'st_bd' => 'required|integer',
                'st_kt' => 'required|integer|gt:st_bd',
                'hoc_ky' => 'required|integer',
                'thu_hoc' => 'required|integer|min:0|max:6'
            ],
            [
                'ma_mh' => "Chọn môn học",
                'phong_hoc' => "Chọn phòng học",
                'ngay_bd' => "Chọn thời gian học",
                'thu_hoc' => "Chọn ngày học",
                'st_bd' => 'Chọn số tiết bắt đầu',
                'st_kt' => 'Chọn số tiết kết thúc',
                'st_kt.gt' => 'Số tiết kết thúc phải lớn hơn số tiết bắt đầu',
                'hoc_ky' => 'Chọn học kỳ',
            ]
        );

        try {

            //kiểm tra nmh
            $nmhExists = LichDay::where('ma_gv', $request->ma_gv)
                ->where('ma_mh', $request->ma_mh)
                ->where('nmh', $request->nmh)
                ->exists();
            if ($nmhExists) {
                return response()->json(['error' => 'Vui lòng chọn nhóm môn học khác.'], 400);
            }

            // kiểm tra phòng học
            // Lấy tất cả các ma_gd của ma_gv
            $lichDays = LichDay::where('ma_gv', $request->ma_gv)->get();
            $tkbEntries = [];
            // return response()->json(['error' => $lichDays], 400);
            foreach ($lichDays as $lichDay) {

                $ngay_bd = Carbon::parse($request->ngay_bd);


                while ($ngay_bd->dayOfWeek !== $request->thu_hoc) {
                    $ngay_bd->addDay();
                }

                $existingTkb = Tkb::where('ma_gd', $lichDay->ma_gd)
                    ->where('ngay_hoc', $ngay_bd->toDateString())
                    ->where(function ($query) use ($request) {
                        $query->whereBetween('st_bd', [$request->st_bd, $request->st_kt])
                            ->orWhereBetween('st_kt', [$request->st_bd, $request->st_kt])
                            ->orWhere(function ($query) use ($request) {
                                $query->where('st_bd', '<=', $request->st_bd)
                                    ->where('st_kt', '>=', $request->st_kt);
                            });
                    })
                    ->exists();

                if ($existingTkb) {
                    return response()->json(['error' => 'Lịch học trùng lặp với lịch học hiện tại.'], 400);
                }

                $ngay_bd->addWeek();
            }

            //
            $lichDay = LichDay::create($request->except('thu_hoc'));
            $tkbEntries = $this->createTkbEntries($lichDay, $request->thu_hoc);
            return response()->json(['message' => 'Lịch giảng dạy được tạo thành công', 'lichDay' => $lichDay, 'tkb' => $tkbEntries], 200);
        } catch (Exception $ex) {
            return response()->json([
                'error' => $ex->getMessage()
            ]);
        }
    }

    private function createTkbEntries($lichDay, $thu_hoc)
    {
        $ngay_bd = Carbon::parse($lichDay->ngay_bd);
        $ngay_kt = Carbon::parse($lichDay->ngay_kt);

        while ($ngay_bd->dayOfWeek !== $thu_hoc) {
            $ngay_bd->addDay();
        }
        // dd($ngay_bd);

        $tkbEntries = [];

        while ($ngay_bd->lte($ngay_kt)) {
            $tkb = Tkb::create([
                'ma_gd' => $lichDay->ma_gd,
                'ngay_hoc' => $ngay_bd->toDateString(),
                'phong_hoc' => $lichDay->phong_hoc,
                'st_bd' => $lichDay->st_bd,
                'st_kt' => $lichDay->st_kt,
                'ghi_chu' => ''
            ]);

            $tkbEntries[] = $tkb;
            $ngay_bd->addWeek();
        }

        return $tkbEntries;
    }
}
