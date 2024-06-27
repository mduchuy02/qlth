<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function getProfile()
    {
        $profile = Auth::user()->username;
        if (!$profile) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }
        $admin = Admin::where('username',$profile)->select("username","email","full_name","role")->firstOrFail();
        return response()->json($admin);
    }
}
