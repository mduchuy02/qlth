<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class PDTController extends Controller
{
    public function getAllTeachers()
    {
        $taikhoangv = User::join('giao_vien', 'users.ma_gv', 'giao_vien.ma_gv')
            ->select('users.ma_gv', 'giao_vien.ten_gv as name', 'giao_vien.ngay_sinh', 'giao_vien.phai', 'giao_vien.dia_chi', 'giao_vien.sdt', 'giao_vien.email')
            ->get();

        $taikhoangv = $taikhoangv->map(function ($item, $key) {
            $item->id = $key + 1;
            return $item;
        });

        return response()->json($taikhoangv);
    }
}
