<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    //
    public function show($id)
    {
        // return User::find($id);
        return User::findOrFail(4);
    }

    public function index()
    {
        $users = User::get();
        return response()->json($users);
    }
}
