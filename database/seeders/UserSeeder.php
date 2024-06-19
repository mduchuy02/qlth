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
            'username' => 'DH52010498',
            'ma_sv' => 'DH52010498',
            'email' =>  'DH52010498@gmail.com',
            'password' => Hash::make('DH52010498'),
            'role' => 'student'
        ]);
    }
}
