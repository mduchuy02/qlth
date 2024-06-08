<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    //
    public function show($id)
    {
        return User::findOrFail($id);
    }

    public function index()
    {
        $users = User::get();
        return response()->json($users);
    }
}
