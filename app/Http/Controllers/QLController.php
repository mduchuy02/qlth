<?php

namespace App\Http\Controllers;

use App\Models\SinhVien;
use Illuminate\Http\Request;

class QLController extends Controller
{
    public function getsv()
    {
        try {
            $sinhvien = SinhVien::findOrFail("DH52010146");
            return response()->json($sinhvien);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
