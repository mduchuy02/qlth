<?php

namespace App\Exports;

use App\Models\DiemDanh;
use App\Models\LichDay;
use App\Models\LichHoc;
use App\Models\SinhVien;
use App\Models\Tkb;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Illuminate\Support\Facades\Auth;

class GiaoVienSheetExport implements WithTitle, WithHeadings, FromCollection, WithMapping
{
    public $magd;
    public $data;
    public $tenmh;
    public $nmh;
    public function __construct($magd)
    {
        $this->magd = $magd;

        $this->data = $this->getDanhSachDiemDanh($magd);
        $lichDay = LichDay::where('ma_gd', $this->magd)->first();

        if ($lichDay) {
            $this->tenmh = $lichDay->monHoc->ten_mh;
            $this->nmh = $lichDay->nmh;
        }
    }

    /**
     * @return string
     */

    public function title(): string
    {
        return ($this->tenmh ?? 'Môn học') . ' ' . ($this->nmh ?? 'NMH');
    }

    public function headings(): array
    {
        if ($this->data->isEmpty()) {
            return [];
        }

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

    public function collection()
    {
        return $this->data;
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

    private function getDanhSachDiemDanh($ma_gd)
    {
        try {
            $ma_gv = Auth::user()->username;

            $tkb = Tkb::where('ma_gd', $ma_gd)
                ->pluck('ma_tkb');

            $sinhviens = LichHoc::where('ma_gd', $ma_gd)
                ->select('ma_sv')
                ->get();
            $sinhviens->map(function ($sinhvien) use ($tkb) {
                $sessions = [];

                foreach ($tkb as $index => $ma_tkb) {
                    $diemDanh = DiemDanh::where('ma_tkb', $ma_tkb)
                        ->where('ma_sv', $sinhvien->ma_sv)
                        ->select('ghi_chu')
                        ->first();

                    $sessions['Buổi ' . ($index + 1)] = $diemDanh && $diemDanh->ghi_chu === 'có phép' ? 'có phép' : '';
                }
                $sinhvien->setAttribute('sessions', $sessions);

                $sinhvien->sbh = $tkb->count();
                $sinhvien->sbdd = DiemDanh::whereIn('ma_tkb', $tkb)
                    ->where('ma_sv', $sinhvien->ma_sv)
                    ->where('ghi_chu', '!=', 'có phép')
                    ->count();
                $sinhvien->ten_sv = SinhVien::where('ma_sv', $sinhvien->ma_sv)
                    ->select('ten_sv')
                    ->first()->ten_sv;
                $sinhvien->sbv = $sinhvien->sbh - $sinhvien->sbdd;
                $sinhvien->ma_lop = SinhVien::where('ma_sv', $sinhvien->ma_sv)
                    ->select('ma_lop')
                    ->first()->ma_lop;

                $sinhvien->cong_diem = DiemDanh::whereIn('ma_tkb', $tkb)
                    ->where('ma_sv', $sinhvien->ma_sv)
                    ->whereNotNull('diem_danh1')
                    ->whereNotNull('diem_danh2')
                    ->count();
                $sinhvien->diemqt = $this->customRound($sinhvien->sbdd * (10 / $tkb->count()));

                return $sinhvien;
            });

            $sinhviens = $sinhviens
                ->sortBy(function ($sinhvien) {
                    $names = explode(' ', $sinhvien->ten_sv);
                    return end($names);
                })
                ->sortBy('ma_lop')
                ->values();

            return $sinhviens;
        } catch (\Exception $e) {
            return collect([
                (object)[
                    'message' => 'Đã xảy ra lỗi khi lấy danh sách sinh viên: ' . $e->getMessage()
                ]
            ]);
        }
    }

    private function customRound($number)
    {
        $intPart = floor($number);
        $decimalPart = $number - $intPart;

        if ($decimalPart < 0.25) {
            return $intPart + 0.0;
        } elseif ($decimalPart < 0.75) {
            return $intPart + 0.5;
        } else {
            return $intPart + 1.0;
        }
    }
}
