<?php

namespace App\Http\Controllers;

use App\Models\Lop;
use Illuminate\Http\Request;

class LopController extends Controller
{
    //
    public function index()
    {
        $lop = Lop::get('ma_lop');
        return response()->json($lop);
    }
}
