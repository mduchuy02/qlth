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
}
