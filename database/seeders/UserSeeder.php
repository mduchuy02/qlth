<?php

namespace Database\Seeders;

use App\Models\GiaoVien;
use App\Models\SinhVien;
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

        // $ma_sv_list = SinhVien::pluck('ma_sv')->toArray();
        // $data = [];
        // foreach ($ma_sv_list as $ma_sv) {
        //     $data[] = [
        //         'username' => $ma_sv,
        //         'ma_sv' => $ma_sv,
        //         'email' => $ma_sv . '@gmail.com', // Có thể tạo email dựa trên ma_gv nếu cần
        //         'password' => Hash::make($ma_sv), // Sử dụng ma_gv làm mật khẩu
        //         'role' => 'student'
        //     ];
        // }
        // DB::table('users')->insert($data);
        // DB::table('users')->insert([
        //     'username' => 'DHTH2889',
        //     'ma_gv' => 'DHTH2889',
        //     'email' =>  'DHTH2889@gmail.com',
        //     'password' => Hash::make('DHTH2889'),
        //     'role' => 'teacher'
        // ]);

        // lich_hoc

        // $ma_sv_list = SinhVien::where('ma_lop', 'D20_TH02')
        //     ->pluck('ma_sv')->toArray();
        // $data = [];
        // foreach ($ma_sv_list as $ma_sv) {
        //     $data[] = [
        //         'ma_sv' => $ma_sv,
        //         'ma_gd' => "83",
        //     ];
        // }
        // DB::table('lich_hoc')->insert($data);


        $ma_sv_list = GiaoVien::pluck('ma_gv')->toArray();
        $data = [];
        foreach ($ma_sv_list as $ma_sv) {
            $data[] = [
                'username' => $ma_sv,
                'ma_gv' => $ma_sv,
                'email' => $ma_sv . '@gmail.com', // Có thể tạo email dựa trên ma_gv nếu cần
                'password' => Hash::make($ma_sv), // Sử dụng ma_gv làm mật khẩu
                'role' => 'teacher'
            ];
        }
        DB::table('users')->insert($data);
    }
}
