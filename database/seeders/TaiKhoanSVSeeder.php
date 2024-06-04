<?php

namespace Database\Seeders;

use App\Models\SinhVien;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TaiKhoanSVSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $sinhviens = SinhVien::all();
        foreach($sinhviens as $sv)
        {
            DB::table('tai_khoan_sv')->insert([
                'ma_sv' => $sv->ma_sv,
                'mat_khau' => Hash::make($sv->ma_sv),
            ]);
        }
    }
}
