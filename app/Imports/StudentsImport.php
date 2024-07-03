<?php

namespace App\Imports;

use App\Models\KetQua;
use App\Models\SinhVien;
use App\Models\Student;
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
        return new SinhVien([

            "ma_sv" => $row['ma_sv'],
            "ten_sv" => $row['ten_sv'],
            "ngay_sinh" => $row['ngay_sinh'],
            "phai" => $row['phai'],
            "dia_chi" => $row['dia_chi'],
            "sdt" => $row['sdt'],
            "email" => $row['email'],
            "anh_qr" => $row['anh_qr'],
            "ma_lop" => $row['ma_lop'],
        ]);
    }
}
