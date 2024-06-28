<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('admin')->insert([
            'username' => 'TE52003592',
            'password' => Hash::make('TE52003592'),
            'email' => 'khaiminh@gmail.com',
            'full_name' => 'Nguyễn Khải Minh',
            'role' => 'teacher_admin',
        ]);
    }
}
