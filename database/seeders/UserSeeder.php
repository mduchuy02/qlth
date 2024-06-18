<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'username' => 'DHTH1528',
            'ma_gv' => 'DHTH1528',
            'email' =>  'DHTH1528@gmail.com',
            'password' => Hash::make('DHTH1528'),
            'role' => 'teacher'
        ]);
    }
}
