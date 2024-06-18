<?php

namespace App\Http\Controllers;

use App\Models\SinhVien;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    // Thông tin cá nhân
    public function information($id)
    {
        $user = SinhVien::findOrFail($id);
        return response()->json($user);
    }
}
