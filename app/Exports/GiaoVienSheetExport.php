<?php

namespace App\Exports;
use App\Models\DiemDanh;
use App\Models\LichDay;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class GiaoVienSheetExport implements WithTitle, WithHeadings, FromCollection, WithMapping
{
    public $magd;
    public $tenmh;
    public function __construct($magd){
        $this->magd = $magd;
        $tenmh = LichDay::join('mon_hoc', 'mon_hoc.ma_mh', 'lich_gd.ma_mh')->where('lich_gd.ma_gd', $this->magd)->select('ten_mh')->first();
        $nmh = LichDay::where('lich_gd.ma_gd', $this->magd)->select('nmh')->first();
        $this->nmh = $nmh->nmh ?? '';
        $this->tenmh = $tenmh->ten_mh ?? '';
    }
    /**
     * @return string
     */
    public function title(): string{
        return $this->tenmh . ' nhóm ' . $this->nmh;
    }
    public function headings ():array {
        return [
            'Mã sinh viên',
            'Tên sinh viên',
            'Số buổi học',
            'Số buổi điểm danh',
            'Số buổi vắng'
        ];
    }
    public function collection()
    {
        $query = LichDay::join('lich_hoc', 'lich_hoc.ma_gd', 'lich_gd.ma_gd')
            ->join('sinh_vien', 'sinh_vien.ma_sv', 'lich_hoc.ma_sv')
            ->join('tkb','tkb.ma_gd','lich_hoc.ma_gd')
            ->leftJoin('diem_danh', function ($join) {
                $join->on('lich_hoc.ma_sv', '=', 'diem_danh.ma_sv')
                    ->on('tkb.ma_tkb', '=', 'diem_danh.ma_tkb');
            })
            ->select(
                'sinh_vien.ma_sv',
                'sinh_vien.ten_sv',
                \DB::raw('COUNT(DISTINCT diem_danh.ma_tkb) as so_buoi_diem_danh'),
                \DB::raw('COUNT(DISTINCT tkb.ma_tkb) as so_buoi_hoc')
            )
            ->where('lich_hoc.ma_gd',$this->magd)
            ->groupBy('sinh_vien.ma_sv', 'sinh_vien.ten_sv')
            ->get();
        // dd($query);
        foreach ($query as $item) {
            $item->so_buoi_vang = $item->so_buoi_hoc - $item->so_buoi_diem_danh;
        }

        return collect($query);
    }
    public function map($row): array {
        return [
            $row['ma_sv'],
            $row['ten_sv'],
            $row['so_buoi_hoc'],
            $row['so_buoi_diem_danh'],
            $row['so_buoi_vang']
        ];
    }
}
