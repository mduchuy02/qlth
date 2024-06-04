<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use Illuminate\Support\Str;
class SinhVienSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
  
     public function run()
{
    $faker = Faker::create('vi_VN');
    $classes = [
        'D20_TH01', 'D20_TH02', 'D20_TH03',
        'D20_DT01', 'D20_DT02', 'D20_DT03',
        'D20_VT01', 'D20_VT02', 'D20_VT03',
        'D20_TP01', 'D20_TP02', 'D20_TP03',
        'D20_XD01', 'D20_XD02', 'D20_XD03',
        'D20_KD01', 'D20_KD02', 'D20_KD03',
        'D20_CN01', 'D20_CN02', 'D20_CN03',
        'D21_TH01', 'D21_TH02', 'D21_TH03',
        'D21_DT01', 'D21_DT02', 'D21_DT03',
        'D21_VT01', 'D21_VT02', 'D21_VT03',
        'D21_TP01', 'D21_TP02', 'D21_TP03',
        'D21_XD01', 'D21_XD02', 'D21_XD03',
        'D21_KD01', 'D21_KD02', 'D21_KD03',
        'D21_CN01', 'D21_CN02', 'D21_CN03'
    ];

    foreach ($classes as $class) {
        for ($i = 1; $i <= 40; $i++) {
            $randomNumber = $faker->unique()->randomNumber(5);
            $ma_sv = 'DH5' . substr($class, 1, 2) . $randomNumber;
            $birthYear = (int)(substr($class, 1, 2)) + 2000 - 18;
            
            // Tạo ngày sinh hợp lệ
            $day = $faker->numberBetween(1, 28);
            if (date('L', mktime(0, 0, 0, 2, $day, $birthYear))) {
                $day = 29;
            }
            $ngay_sinh = $birthYear . '-' . '02' . '-' . sprintf("%02d", $day);

            DB::table('sinh_vien')->insert([
                'ma_sv' => $ma_sv,
                'ten_sv' => $faker->name,
                'ngay_sinh' => $ngay_sinh,
                'phai' => $faker->numberBetween(0, 1),
                'dia_chi' => $faker->address,
                'sdt' => $faker->unique()->numerify('0#########'),
                'email' => Str::random(10).'@gmail.com',
                'anh_qr' => $ma_sv.'.png',
                'ma_lop' => $class,
            ]);
        }
    }
}


}
