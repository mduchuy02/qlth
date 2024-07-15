<?php

namespace App\Http\Controllers;

use App\Models\KetQua;
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
}
