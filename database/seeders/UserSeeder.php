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
            'username' => 'DHCN2787',
            'ma_gv' => 'DHCN2787',
            'email' =>  'DHCN2787@gmail.com',
            'password' => Hash::make('DHCN2787'),
            'role' => 'teacher'
        ]);
    }
}
