<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
class GiaoVienSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create('vi_VN');
        for ($i = 1; $i <= 5; $i++) {
            $randomNumber = $faker->unique()->randomNumber(4);
            DB::table('giao_vien')->insert([
                'ma_gv' => 'DHXD'.$randomNumber,
                'ten_gv' => $faker->name,
                'ngay_sinh' => now()->subYears(rand(25, 55)),
                'phai' => $faker->numberBetween(0, 1),
                'dia_chi' => $faker->address,
                'sdt' => $faker->unique()->numerify('0#########'),
                'email' => Str::random(10).'@gmail.com',
            ]);
        }
    }
}
