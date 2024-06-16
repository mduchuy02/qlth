<?php

namespace App\Http\Controllers;

use App\Models\LichDay;
use Illuminate\Http\Request;

class LichDayController extends Controller
{
    public function show($id)
    {
        return LichDay::where('ma_gv', $id)->get();
    }

    public function getHocKy($ma_gv)
    {
        $lichDays = LichDay::where('ma_gv', $ma_gv)->get();
        $thoiGianHoc = [];
        foreach ($lichDays as $lichDay) {
            $thoiGian = explode('-', $lichDay->thoi_gian);
            $ngayBatDau = date_create_from_format('d/m/Y', trim($thoiGian[0]));
            $month = $ngayBatDau->format('m');
            switch ($month) {
                case 9:
                case 10:
                case 11:
                case 12:
                case 1:
                    $hoc_ky = 1;
                    break;
                case 2:
                case 3:
                case 4:
                case 5:
                case 6:
                    $hoc_ky = 2;
                    break;
                case 7:
                case 8:
                    $hoc_ky = 3;
                    break;
            }
            $year = $ngayBatDau->format('Y');
            $lichDay->hoc_ky = "Học kỳ " . $hoc_ky . " năm học " . $year . "-" . ($year + 1);
            array_push($thoiGianHoc, $lichDay->hoc_ky);
        }
        return $thoiGianHoc;
    }

    public function getLichGD(Request $request, $magv)
    {
        $hocKyNam = $request->query('hoc_ky');

        // Tách hocKyNam thành học kỳ và năm
        $hoc_ky = substr($hocKyNam, 0, 1);
        $nam = substr($hocKyNam, 1);

        // Tính toán thời gian từ học kỳ và năm
        $thoi_gian = $this->calculateThoiGianFromHocKyNam($hoc_ky, $nam);

        // Lấy dữ liệu từ DB dựa trên mã giáo viên và thời gian
        $lichGD = LichDay::where('ma_gv', $magv)
            ->join('mon_hoc', 'lich_gd.ma_mh', '=', 'mon_hoc.ma_mh')
            ->select(
                'lich_gd.ma_gd as key',
                'lich_gd.ma_mh as MaMH',
                'mon_hoc.ten_mh as TenMH',
                'mon_hoc.so_tiet as SoTiet',
                'lich_gd.st_bd as TietBD',
                'lich_gd.st_kt as ST',
                'lich_gd.phong_hoc as Phong',
                'lich_gd.thoi_gian as Tuan'
            )
            ->get()
            ->filter(function ($item) use ($thoi_gian) {
                // Kiểm tra xem thời gian của mỗi bản ghi có nằm trong khoảng thời gian đã tính được không
                $thoi_gian_db = explode('-', $item->Tuan);
                $start_date = date_create_from_format('d/m/Y', trim($thoi_gian_db[0]));
                $end_date = date_create_from_format('d/m/Y', trim($thoi_gian_db[1]));
                $query_start_date = date_create_from_format('d/m/Y', trim($thoi_gian[0]));

                return ($query_start_date >= $start_date && $query_start_date <= $end_date);
            });
        return response()->json($lichGD);
    }

    private function calculateThoiGianFromHocKyNam($hoc_ky, $nam)
    {
        $currentYear = date('Y');
        $nam = $nam + 2000;

        switch ($hoc_ky) {
            case 1: // Học kỳ 1 từ tháng 9 năm trước đến tháng 1 năm sau
                $startDate = date_create_from_format('Y-m-d', $nam . '-09-30');
                break;
            case 2: // Học kỳ 2 từ tháng 2 đến tháng 6
                $startDate = date_create_from_format('Y-m-d', $nam . '-03-01');
                break;
            case 3: // Học kỳ 3 từ tháng 7 đến tháng 8
                $startDate = date_create_from_format('Y-m-d', $nam . '-07-07');
                break;
            default:
                return null; // Trường hợp không hợp lệ
        }

        return [
            $startDate->format('d/m/Y'),
        ];
    }
}
