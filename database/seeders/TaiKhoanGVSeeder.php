<?php

namespace Database\Seeders;

use App\Models\GiaoVien;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TaiKhoanGVSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $giaoviens = GiaoVien::all();
        foreach ($giaoviens as $gv) {
            DB::table('tai_khoan_gv')->insert([
                'ma_gv' => $gv->ma_gv,
                'mat_khau' => Hash::make($gv->ma_gv),
            ]);
        }
    }
}
