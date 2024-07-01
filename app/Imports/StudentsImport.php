<?php

namespace App\Imports;

use App\Models\KetQua;
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
        return new KetQua([

            "ma_sv" => $row['ma_sv'],
            "ma_mh" => $row['ma_mh'],
            "diem_qt" => $row['diem_qt'],
            "diem_thi1" => $row['diem_thi1'],
            "diem_thi2" => $row['diem_thi2'],
            "diem_tb" => $row['diem_tb'],
        ]);
    }
}
