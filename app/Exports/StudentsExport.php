<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StudentsExport implements FromCollection, WithHeadings, WithMapping
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
            'Key', 'Tên SV', 'Ngày Sinh', 'Mã Sinh Viên', 'Tên Lớp', 'Tên Khoa'
        ];
    }
    public function map($row): array
    {
        return [
            $row['key'],
            $row['ten_sv'],
            $row['ngay_sinh'],
            $row['ma_sv'],
            $row['ten_lop'],
            $row['ten_khoa']
        ];
    }
}
