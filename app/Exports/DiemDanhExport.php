<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class DiemDanhExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    protected $data;
    protected $tenmh;
    protected $nmh;
    public function __construct($data, $tenmh, $nmh)
    {
        $this->data = $data;
        $this->tenmh = $tenmh;
        $this->nmh = $nmh;
    }

    public function collection()
    {
        return collect($this->data);
    }

    public function headings(): array
    {
        $sinhvien = $this->data->first();
        $sessionHeadings = array_keys($sinhvien->sessions);

        return array_merge([
            'Tên sinh viên',
            'Mã sinh viên',
            'Tên lớp',
            'Số buổi học',
        ], $sessionHeadings, [
            'Số Buổi có mặt',
            'Số buổi vắng',
            'Số buổi điểm danh 2 lần',
            'Điểm quá trình',
        ]);
    }

    public function map($row): array
    {
        $sessions = array_values($row->sessions);
        return array_merge([
            $row['ten_sv'],
            $row['ma_sv'],
            $row['ma_lop'],
            $row['sbh'],

        ], $sessions, [
            $row['sbdd'],
            $row['sbv'],
            $row['cong_diem'],
            $row['diemqt'],
        ]);
    }
    public function title(): string
    {
        return $this->tenmh . ' ' . $this->nmh;
    }
}
