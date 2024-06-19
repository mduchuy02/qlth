<?php

namespace App\Http\Controllers;

use App\Models\SinhVien;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SinhVienController extends Controller
{
    public function information($id)
    {
        $user = SinhVien::findOrFail($id);
        return response()->json($user);
    }
    public function profile()
    {
        try {
            $ma_sv = Auth::user()->username;
            if (!$ma_sv) {
                return response()->json(['error' => 'Unauthenticated.'], 401);
            }
            return response()->json(SinhVien::findOrFail($ma_sv));
        } catch (\Exception $e) {
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }
}
