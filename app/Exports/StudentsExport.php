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
    private $rowNumber = 0;
    public function headings(): array
    {
        return [
            'STT',
            'Mã Sinh Viên',
            'Tên SV',
            'Ngày Sinh',
            'Giới tính',
            'Địa chỉ',
            'Tên Lớp',
            'Tên Khoa'
        ];
    }
    public function map($row): array
    {
        $this->rowNumber++;
        return [
            $this->rowNumber,
            $row['ma_sv'],
            $row['ten_sv'],
            $row['ngay_sinh'],
            $row['phai'],
            $row['dia_chi'],
            $row['ten_lop'],
            $row['ten_khoa']
        ];
    }
}
