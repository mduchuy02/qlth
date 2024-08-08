<?php

namespace App\Http\Controllers;


use App\Models\DiemDanh;
use App\Models\GiaoVien;
use App\Models\LichDay;
use App\Models\LichHoc;
use App\Models\MonHoc;
use App\Models\SinhVien;
use App\Models\Tkb;

use App\Exports\GiaoVienExport;

use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GiaoVienController extends Controller
{
    public function profile()
    {
        try {
            $ma_gv = Auth::user()->username;
            if (!$ma_gv) {
                return response()->json(['error' => 'Unauthenticated.'], 401);
            }
            return response()->json(GiaoVien::findOrFail($ma_gv));
        } catch (\Exception $e) {
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }

    public function store(Request $request)
    {
        $ma_gv = $request->ma_gv;
        $request->validate(
            [
                "email" => "required|email|unique:giao_vien,email,$ma_gv,ma_gv",
                "sdt" => "required|numeric|digits_between:10,11,|unique:giao_vien,sdt,$ma_gv,ma_gv",
                'password' => 'sometimes|nullable|string|min:8|confirmed'
            ],
            [
                "email.required" => "Nhập email",
                'email.email' => 'Email không hợp lệ',
                'email.max' => 'Email không được vượt quá 50 ký tự',
                'email.unique' => 'Email đã tồn tại',

                "sdt.required" => "Nhập số điện thoại",
                "sdt.numeric" => "Số điện thoại chỉ chứa số",
                "sdt.digits_between" => "Số điện thoại không hợp lệ",
                "sdt.unique" => 'Số điện thoại đã tồn tại',
                'password.confirmed' => 'Mật khẩu xác nhận không trùng khớp',
            ]
        );
        $update = GiaoVien::where('ma_gv', $ma_gv)
            ->update([
                'email' => $request->email,
                'sdt' => $request->sdt
            ]);
        if ($request->filled('password')) {
            $taikhoangv = User::where('username', $ma_gv)
                ->update([
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                ]);
            if ($update && $taikhoangv) {
                return response()->json(['message' => 'Cập nhật thông tin giáo viên thành công!'], 200);
            }
        } else {
            $taikhoangv = User::where('username', $ma_gv)
                ->update([
                    'email' => $request->email
                ]);
            if ($update && $taikhoangv) {
                return response()->json(['message' => 'Cập nhật thông tin giáo viên thành công!'], 200);
            }
        }
    }

    public function getSubject()
    {
        $monHoc = MonHoc::select('ma_mh', 'ten_mh')->orderBy('ten_mh')->get();
        return response()->json($monHoc);
    }

    public function createSchedule(Request $request)
    {
        $request->validate(
            [
                'ma_gv' => 'required|string|max:10',
                'ma_mh' => 'required|string|max:20',
                'nmh' => 'required|integer',
                'phong_hoc' => 'required|string|max:10',
                'ngay_bd' => 'required|date',
                'ngay_kt' => 'required|date',
                'st_bd' => 'required|integer',
                'st_kt' => 'required|integer|gt:st_bd',
                'hoc_ky' => 'required|integer',
                'thu_hoc' => 'required|integer|min:0|max:6'
            ],
            [
                'ma_mh' => "Chọn môn học",
                'phong_hoc' => "Chọn phòng học",
                'ngay_bd' => "Chọn thời gian học",
                'thu_hoc' => "Chọn ngày học",
                'st_bd' => 'Chọn số tiết bắt đầu',
                'st_kt' => 'Chọn số tiết kết thúc',
                'st_kt.gt' => 'Số tiết kết thúc phải lớn hơn số tiết bắt đầu',
                'hoc_ky' => 'Chọn học kỳ',
            ]
        );

        try {

            //kiểm tra nmh
            $nmhExists = LichDay::where('ma_gv', $request->ma_gv)
                ->where('ma_mh', $request->ma_mh)
                ->where('nmh', $request->nmh)
                ->exists();
            if ($nmhExists) {
                return response()->json(['error' => 'Vui lòng chọn nhóm môn học khác.'], 400);
            }

            // kiểm tra phòng học
            // Lấy tất cả các ma_gd của ma_gv
            $lichDays = LichDay::where('ma_gv', $request->ma_gv)->get();
            $tkbEntries = [];
            // return response()->json(['error' => $lichDays], 400);
            foreach ($lichDays as $lichDay) {

                $ngay_bd = Carbon::parse($request->ngay_bd);
                while ($ngay_bd->dayOfWeek !== $request->thu_hoc) {
                    $ngay_bd->addDay();
                }

                $existingTkb = Tkb::where('ma_gd', $lichDay->ma_gd)
                    ->where('ngay_hoc', $ngay_bd->toDateString())
                    ->where(function ($query) use ($request) {
                        $query->whereBetween('st_bd', [$request->st_bd, $request->st_kt])
                            ->orWhereBetween('st_kt', [$request->st_bd, $request->st_kt])
                            ->orWhere(function ($query) use ($request) {
                                $query->where('st_bd', '<=', $request->st_bd)
                                    ->where('st_kt', '>=', $request->st_kt);
                            });
                    })
                    ->exists();

                if ($existingTkb) {
                    return response()->json(['error' => 'Lịch học trùng lặp với lịch học hiện tại.'], 400);
                }

                $ngay_bd->addWeek();
            }

            //
            $lichDay = LichDay::create($request->except('thu_hoc'));
            $tkbEntries = $this->createTkbEntries($lichDay, $request->thu_hoc);
            return response()->json(['message' => 'Lịch giảng dạy được tạo thành công', 'lichDay' => $lichDay, 'tkb' => $tkbEntries], 200);
        } catch (Exception $ex) {
            return response()->json([
                'error' => $ex->getMessage()
            ]);
        }
    }
    private function createTkbEntries($lichDay, $thu_hoc)
    {
        $ngay_bd = Carbon::parse($lichDay->ngay_bd);
        $ngay_kt = Carbon::parse($lichDay->ngay_kt);

        while ($ngay_bd->dayOfWeek !== $thu_hoc) {
            $ngay_bd->addDay();
        }
        // dd($ngay_bd);

        $tkbEntries = [];

        while ($ngay_bd->lte($ngay_kt)) {
            $tkb = Tkb::create([
                'ma_gd' => $lichDay->ma_gd,
                'ngay_hoc' => $ngay_bd->toDateString(),
                'phong_hoc' => $lichDay->phong_hoc,
                'st_bd' => $lichDay->st_bd,
                'st_kt' => $lichDay->st_kt,
                'ghi_chu' => ''
            ]);

            $tkbEntries[] = $tkb;
            $ngay_bd->addWeek();
        }

        return $tkbEntries;
    }

    public function teachingSchedule($ma_gv)
    {
        $schedules = LichDay::where('ma_gv', $ma_gv)
            ->with('monHoc')
            ->withCount('lichHocs')
            ->join('mon_hoc', 'lich_gd.ma_mh', '=', 'mon_hoc.ma_mh')
            ->orderBy('mon_hoc.ma_mh')
            ->orderBy('lich_gd.nmh')
            ->get()
            ->map(function ($lichDay) {
                return [
                    'ma_gd' => $lichDay->ma_gd,
                    'ma_mh' => $lichDay->monHoc->ma_mh,
                    'ten_mh' => $lichDay->monHoc->ten_mh,
                    'nmh' => $lichDay->nmh,
                    'phong_hoc' => $lichDay->phong_hoc,
                    'ngay_bd' => $lichDay->ngay_bd,
                    'ngay_kt' => $lichDay->ngay_kt,
                    'st_bd' => $lichDay->st_bd,
                    'st_kt' => $lichDay->st_kt,
                    'hoc_ky' => $lichDay->hoc_ky,
                    'so_luong_sinh_vien' => $lichDay->lich_hocs_count,
                ];
            });

        return response()->json($schedules);
    }

    public function getSchedule($ma_gd)
    {

        $lichDay = LichDay::with('tkbs')
            ->where('ma_gd', $ma_gd)
            ->first();

        if (!$lichDay) {
            return response()->json(['error' => 'Lịch giảng dạy không tồn tại'], 404);
        }

        // Trả về dữ liệu thời khóa biểu
        $tkbs = $lichDay->tkbs->map(function ($tkb) {
            return [
                'ma_tkb' => $tkb->ma_tkb,
                'ngay_hoc' => $tkb->ngay_hoc,
                'phong_hoc' => $tkb->phong_hoc,
                'st_bd' => $tkb->st_bd,
                'st_kt' => $tkb->st_kt,
            ];
        });
        return response()->json($tkbs);
    }


    public function getStudent($ma_gd)
    {
        $students = LichHoc::with(['sinhVien.lop'])
            ->where('ma_gd', $ma_gd)
            ->join('sinh_vien', 'lich_hoc.ma_sv', '=', 'sinh_vien.ma_sv')
            ->join('lop', 'sinh_vien.ma_lop', '=', 'lop.ma_lop')
            ->select(
                'sinh_vien.ma_sv',
                'sinh_vien.ten_sv',
                'sinh_vien.ngay_sinh',
                'sinh_vien.phai',
                'lop.ten_lop'
            )
            ->orderBy('lop.ten_lop')
            ->orderByRaw("SUBSTRING_INDEX(sinh_vien.ten_sv,' ', -1)")
            ->get();

        if ($students->isEmpty()) {
            return response()->json([], 200);
        }
        return response()->json($students);
    }

    public function deleteTeachingSchedule($ma_gd)
    {
        try {
            DB::transaction(function () use ($ma_gd) {

                $tkbs = Tkb::where('ma_gd', $ma_gd)->get();

                foreach ($tkbs as $tkb) {
                    $tkb->diemDanh()->delete();
                    $tkb->qrcode()->delete();
                }
                Tkb::where('ma_gd', $ma_gd)->delete();
                LichHoc::where('ma_gd', $ma_gd)->delete();
                LichDay::where('ma_gd', $ma_gd)->delete();
            });
            return response()->json([
                'message' => "Xóa thành công",
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Không có bản ghi'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Đã xảy ra lỗi khi xóa lịch giảng dạy'], 500);
        }
    }

    public function getStudentDetails(Request $request)
    {
        $studentIDs = $request->input('student_ids');
        $students = SinhVien::whereIn('ma_sv', $studentIDs)
            ->with('lop')
            ->join('lop', 'sinh_vien.ma_lop', '=', 'lop.ma_lop')
            ->select(
                'sinh_vien.ma_sv',
                'sinh_vien.ten_sv',
                'sinh_vien.ngay_sinh',
                'sinh_vien.phai',
                'sinh_vien.ma_lop',
                'lop.ten_lop'
            )
            ->orderBy('lop.ten_lop')
            ->orderByRaw("SUBSTRING_INDEX(sinh_vien.ten_sv,' ', -1)")
            ->get();

        return response()->json($students);
    }


    public function addStudents(Request $request)
    {
        $ma_gd = $request->input('ma_gd');
        $student_ids = $request->input('student_ids');

        try {
            foreach ($student_ids as $ma_sv) {
                // Kiểm tra nếu bản ghi đã tồn tại
                if (!LichHoc::where('ma_sv', $ma_sv)->where('ma_gd', $ma_gd)->exists()) {
                    // Nếu không tồn tại, thêm bản ghi mới
                    LichHoc::create([
                        'ma_sv' => $ma_sv,
                        'ma_gd' => $ma_gd,
                    ]);
                }
            }
            return response()->json(['message' => 'Thêm Thành công']);
        } catch (Exception $e) {
            // Xử lý lỗi và trả về thông báo lỗi
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }
    public function deleteStudent(Request $request)
    {

        $ma_sv = $request->input('ma_sv');
        $ma_gd = $request->input('ma_gd');

        try {
            $tkbs = Tkb::where('ma_gd', $ma_gd)->get();

            foreach ($tkbs as $tkb) {
                DiemDanh::where('ma_sv', $ma_sv)
                    ->where('ma_tkb', $tkb->ma_tkb)
                    ->delete();
            }
            LichHoc::where('ma_sv', $ma_sv)
                ->where('ma_gd', $ma_gd)
                ->delete();
            return response()->json(['message' => 'Xóa thành công.']);
        } catch (Exception $e) {
            // Xử lý lỗi và trả về thông báo lỗi
            return response()->json(['error' => 'Đã xảy ra lỗi: ' . $e->getMessage()], 500);
        }
    }

    public function editSchedule(Request $request)
    {
        $ma_gd = $request->input('ma_gd');

        // Lấy thông tin của ma_gd từ bảng lich_gd
        $schedule = LichDay::with('monHoc')
            ->where('ma_gd', $ma_gd)
            ->first();

        // Kiểm tra xem có tìm thấy lịch không
        if (!$schedule) {
            return response()->json(['message' => 'Không tìm thấy lịch dạy.'], 404);
        }

        // Trả về thông tin chi tiết của ma_gd
        return response()->json($schedule);
    }

    public function saveSchedule(Request $request)
    {
        $ma_gd = $request->input('ma_gd');
        $phong_hoc = $request->input('phong_hoc');
        $request->validate(
            [
                'phong_hoc' => 'required',
            ],
            [
                'phong_hoc' => 'Phòng học không được để trống',
            ]
        );

        // Cập nhật phong_hoc trong bảng lich_gd
        $schedule = LichDay::where('ma_gd', $ma_gd)->first();
        if ($schedule) {
            $schedule->phong_hoc = $phong_hoc;
            $schedule->save();
        } else {
            return response()->json(['message' => 'Không tìm thấy lịch dạy.'], 404);
        }

        // Cập nhật phong_hoc trong bảng tkb
        Tkb::where('ma_gd', $ma_gd)->update(['phong_hoc' => $phong_hoc]);

        return response()->json(['message' => 'Cập nhật thành công.']);
    }

    //custtom ****************************************************

    public function createScheduleCustom(Request $request)
    {
        $request->validate(
            [
                'ma_gv' => 'required|string|max:10',
                'ma_mh' => 'required|string|max:20',
                'nmh' => 'required|integer',
                'phong_hoc' => 'required|string|max:10',
                'ngay_bd' => 'required|date',
                'ngay_kt' => 'required|date',
                'hoc_ky' => 'required|integer',
                'buoi_hoc' => 'required|array',
                'buoi_hoc.*.phong_hoc' => 'required|string|max:10',
                'buoi_hoc.*.st_bd' => 'required|integer',
                'buoi_hoc.*.st_kt' => 'required|integer|gt:buoi_hoc.*.st_bd',
                'buoi_hoc.*.thu_hoc' => 'required|integer|min:0|max:6',
            ],
            [
                'ma_mh.required' => "Chọn môn học",
                'phong_hoc.required' => "Chọn phòng học",
                'ngay_bd.required' => "Chọn thời gian học",
                'hoc_ky' => 'Chọn học kỳ',

                'buoi_hoc.*.thu_hoc.required' => "Chọn ngày học",
                'buoi_hoc.*.st_bd.required' => 'Chọn số tiết bắt đầu',
                'buoi_hoc.*.st_kt.required' => 'Chọn số tiết kết thúc',
                'buoi_hoc.*.st_kt.gt' => 'Số tiết kết thúc phải lớn hơn số tiết bắt đầu',
                'hoc_ky.required' => 'Chọn học kỳ',
            ]
        );

        try {
            // Kiểm tra nmh
            $nmhExists = LichDay::where('ma_gv', $request->ma_gv)
                ->where('ma_mh', $request->ma_mh)
                ->where('nmh', $request->nmh)
                ->exists();
            if ($nmhExists) {
                return response()->json(['error' => 'Vui lòng chọn nhóm môn học khác.'], 400);
            }

            // Lấy tất cả các ma_gd của giáo viên
            $lichDays = LichDay::where('ma_gv', $request->ma_gv)->get();

            foreach ($lichDays as $lichDay) {
                foreach ($request->buoi_hoc as $buoi) {
                    $ngay_bd = Carbon::parse($request->ngay_bd);
                    while ($ngay_bd->dayOfWeek !== $buoi['thu_hoc']) {
                        $ngay_bd->addDay();
                    }

                    while ($ngay_bd->lte(Carbon::parse($request->ngay_kt))) {
                        $existingTkb = Tkb::where('ma_gd', $lichDay->ma_gd)
                            ->where('ngay_hoc', $ngay_bd->toDateString())
                            ->where(function ($query) use ($buoi) {
                                $query->whereBetween('st_bd', [$buoi['st_bd'], $buoi['st_kt']])
                                    ->orWhereBetween('st_kt', [$buoi['st_bd'], $buoi['st_kt']])
                                    ->orWhere(function ($query) use ($buoi) {
                                        $query->where('st_bd', '<=', $buoi['st_bd'])
                                            ->where('st_kt', '>=', $buoi['st_kt']);
                                    });
                            })
                            ->exists();

                        if ($existingTkb) {
                            return response()->json(['error' => 'Lịch học trùng lặp với lịch học hiện tại.'], 400);
                        }

                        $ngay_bd->addWeek();
                    }
                }
            }

            // Tạo mới LichDay entry
            $lichDay = LichDay::create($request->except('buoi_hoc'));

            // Tạo mới Tkb entries cho từng buổi học
            $tkbEntries = [];
            foreach ($request->buoi_hoc as $buoi) {
                $tkbEntries = array_merge($tkbEntries, $this->createTkbEntriesCustom($lichDay, $buoi));
            }

            return response()->json(['message' => 'Lịch giảng dạy được tạo thành công', 'lichDay' => $lichDay, 'tkb' => $tkbEntries], 201);
        } catch (Exception $ex) {
            return response()->json([
                'error' => $ex->getMessage()
            ]);
        }
    }


    private function createTkbEntriesCustom($lichDay, $buoi)
    {
        $ngay_bd = Carbon::parse($lichDay->ngay_bd);
        $ngay_kt = Carbon::parse($lichDay->ngay_kt);

        while ($ngay_bd->dayOfWeek !== $buoi['thu_hoc']) {
            $ngay_bd->addDay();
        }

        $tkbEntries = [];

        while ($ngay_bd->lte($ngay_kt)) {
            $tkb = Tkb::create([
                'ma_gd' => $lichDay->ma_gd,
                'ngay_hoc' => $ngay_bd->toDateString(),
                'phong_hoc' => $buoi['phong_hoc'],
                'st_bd' => $buoi['st_bd'],
                'st_kt' => $buoi['st_kt'],
                'ghi_chu' => ''
            ]);

            $tkbEntries[] = $tkb;
            $ngay_bd->addWeek();
        }

        return $tkbEntries;
    }

    public function export()
    {
        try {
            $ma_gv = Auth::user()->username;
            $ma_gd = LichDay::where('ma_gv', $ma_gv)->pluck('ma_gd');
            if (!$ma_gv) {
                return response()->json(['error' => 'Unauthenticated.'], 401);
            }
            return (new GiaoVienExport($ma_gd))->download('test.xlsx');
        } catch (\Exception $e) {
            return response()->json(['error' => 'Something went wrong'], 500);
        }

    }
}
