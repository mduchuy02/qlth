<?php

namespace App\Http\Controllers;

use App\Models\KetQua;
use App\Models\SinhVien;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class KetQuaController extends Controller
{
    //
    public function getDiem()
    {
        try {
            $ma_sv = Auth::user()->username;
            if (!$ma_sv) {
                return response()->json(['error' => 'Unauthenticated.'], 401);
            }
            $diem = KetQua::join('mon_hoc', 'mon_hoc.ma_mh', 'ket_qua.ma_mh')
                ->where('ma_sv', $ma_sv)
                ->get();

            $diemWithIndex = $diem->map(function ($item, $index) {
                $item->index = $index + 1;
                $item->ket_qua = $item->diem_tb >= 5 ? 'Đạt' : 'Không đạt';
                return $item;
            });

            return response()->json($diemWithIndex);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }


}
