<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DiemDanhExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @return \Illuminate\Support\Collection
     */
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }
    public function collection()
    {
        return collect($this->data);
    }

    public function headings(): array
    {
        return [
            'Tên SV',
            'Mã Sinh Viên',
            'Tên Lớp',
            'Số buổi học',
            'Số buổi điểm danh',
            'Số buổi vắng',
            'Điểm quá trình',
        ];
    }
    public function map($row): array
    {
        return [
            $row['ten_sv'],
            $row['ma_sv'],
            $row['ma_lop'],
            $row['sbh'],
            $row['sbdd'],
            $row['sbv'],
            $row['diemqt'],
        ];
    }
}
