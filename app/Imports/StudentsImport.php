<?php

namespace App\Imports;


use App\Models\SinhVien;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StudentsImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        if (!empty($row['ma_sv'])) {

            $sinhVien = SinhVien::updateOrCreate(
                ['ma_sv' => $row['ma_sv']],
                [
                    "ten_sv" => $row['ten_sv'],
                    "ngay_sinh" => $row['ngay_sinh'],
                    "phai" => $row['phai'],
                    "dia_chi" => $row['dia_chi'],
                    "sdt" => $row['sdt'],
                    "email" => $row['email'],
                    "anh_qr" => $row['anh_qr'],
                    "ma_lop" => $row['ma_lop'],
                ]
            );

            User::updateOrCreate(
                ['ma_sv' => $row['ma_sv']],
                [
                    'username' => $row['ma_sv'],
                    'password' => Hash::make($row['ma_sv']),
                    'email' => $row['email'],
                    'role' => "student",
                ]
            );
            return $sinhVien;
        } else {
            return null;
        }
    }
}
