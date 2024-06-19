<?php

namespace App\Http\Controllers;

use App\Models\LichHoc;
use App\Models\Tkb;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KQDiemDanhController extends Controller
{
    // lấy mã QR code sinh vien
    public function getAttendance($ma_gd, $ma_sv)
    {
        try {

            $tkbs = Tkb::where('ma_gd', $ma_gd)
                ->whereHas('diemDanh', function ($query) use ($ma_sv) {
                    $query->where('ma_sv', $ma_sv);
                })
                ->with(['diemDanh' => function ($query) use ($ma_sv) {
                    $query->where('ma_sv', $ma_sv);
                }])
                ->get();
            if ($tkbs->isEmpty()) {
                return response()->json([]);
            }
            return response()->json($tkbs);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }
    // lấy danh sách môn học
    public function getTimeTable()
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
}
