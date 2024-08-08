<?php

namespace App\Http\Controllers;

use App\Models\KetQua;
use App\Models\Khoa;
use Illuminate\Http\Request;

class PDTThongKeController extends Controller
{
    public function diemHocTap()
    {
        $data = [
            '<5' => KetQua::where('diem_tb', '<', 5)->count(),
            '>=5 và <8' => KetQua::where('diem_tb', '>=', 5)->where('diem_tb', '<', 8)->count(),
            '>=8' => KetQua::where('diem_tb', '>', 8)->count()
        ];
        return response()->json([
            'data' => $data
        ]);
    }

    public function soLuongSinhVien()
    {
        $khoas = Khoa::with(['lops.sinhViens'])->get();


        $thongKe = $khoas->map(function ($khoa) {
            $soLuongSinhVien = $khoa->lops->flatMap(function ($lop) {
                return $lop->sinhViens;
            })->count();

            return [
                'ma_khoa' => $khoa->ma_khoa,
                'so_luong_sinh_vien' => $soLuongSinhVien,
            ];
        });
        return response()->json($thongKe);
    }
}
