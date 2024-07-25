<?php

namespace Database\Seeders;

use App\Models\SinhVien;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class LichHocSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $ds_masv = SinhVien::where('ma_lop', 'D20_TH02')->pluck('ma_sv')->toArray();
        // $data = [];
        // foreach ($ds_masv as $ma_sv) {
        //     $data[] = [
        //         'ma_sv' => $ma_sv,
        //         'ma_gd' => '89'
        //     ];
        // }
        // DB::table('lich_hoc')->insert($data);

        $ds_masv = SinhVien::where('ma_lop', 'D20_TH02')->pluck('ma_sv')->toArray();
        $data = [];
        foreach ($ds_masv as $ma_sv) {
            $data[] = [
                'ma_sv' => $ma_sv,
                'ma_gd' => '93',
            ];
        }
        DB::table('lich_hoc')->insert($data);
    }
}
