<?php

namespace App\Http\Controllers;

use App\Models\LichDay;
use App\Models\LichHoc;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TKBController extends Controller
{
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
