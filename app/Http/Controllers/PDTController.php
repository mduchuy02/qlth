<?php

namespace App\Http\Controllers;

use App\Exports\DiemDanhExport;
use App\Exports\StudentsExport;
use App\Imports\StudentsImport;
use App\Models\GiaoVien;
use App\Models\Khoa;
use App\Models\Lop;
use App\Models\MonHoc;
use App\Models\SinhVien;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Validation\Rule;


class PDTController extends Controller
{
    // public function getAllTeachers()
    // {
    //     $taikhoangv = User::join('giao_vien', 'users.ma_gv', 'giao_vien.ma_gv')
    //         ->select('users.ma_gv', 'giao_vien.ten_gv as name', 'giao_vien.ngay_sinh', 'giao_vien.phai', 'giao_vien.dia_chi', 'giao_vien.sdt', 'giao_vien.email')
    //         ->get();

    //     $taikhoangv = $taikhoangv->map(function ($item, $key) {
    //         $item->id = $key + 1;
    //         return $item;
    //     });
    //     return response()->json($taikhoangv);
    // }
    //
    public function getListDepartmentClass()
    {
        $departmenList = Khoa::select('ma_khoa', 'ten_khoa')
            ->orderBy('ten_khoa')
            ->get();

        $classList = Lop::select('ma_lop', 'ten_lop')
            ->orderBy('ten_lop')
            ->get();
        return response()->json([
            'departmentList' => $departmenList,
            'classList' => $classList
        ]);
    }
    // lọc lớp học
    public function getClass($ma_khoa)
    {
        $classFilter = Lop::where('ma_khoa', $ma_khoa)
            ->select('ma_lop', 'ten_lop')
            ->orderBy('ten_lop')
            ->get();
        return response()->json([
            'classFilter' => $classFilter
        ]);
    }
    // lọc sinh viên theolop
    // public function getListStudent(Request $request)
    // {
    //     try {
    //         $selectedClasses = $request->input('selectedClasses', []);
    //         $nameFilter = $request->input('nameFilter', '');
    //         $idFilter = $request->input('idFilter', '');
    //         $department = $request->input('department', '');

    //         $query = SinhVien::whereIn('ma_lop', $selectedClasses)
    //             ->with('lop.khoa')
    //             ->select('ma_sv', 'ten_sv', 'ngay_sinh', 'ma_lop');
    //         if (!empty($nameFilter)) {
    //             $query->where('ten_sv', 'like', '%' . $nameFilter . '%');
    //         }

    //         if (!empty($idFilter)) {
    //             $query->where('ma_sv', 'like', '%' . $idFilter . '%');
    //         }

    //         $students = $query->get();
    //         if ($students->isEmpty()) {
    //             return response()->json(['message' => 'Không tìm thấy sinh viên.'], 404);
    //         }
    //         $students = $students
    //             ->map(function ($student, $index) {
    //                 $student->key = $index + 1;
    //                 $student->ten_lop = $student->lop->ten_lop;
    //                 $student->ten_khoa = $student->lop->khoa->ten_khoa;
    //                 unset($student->lop);
    //                 return $student;
    //             });
    //         $students = $students->sortBy(function ($student) {
    //             $names = explode(' ', $student->ten_sv);
    //             return end($names);
    //         })->sortBy('ten_lop')->values();

    //         return response()->json($students);
    //     } catch (\Exception $e) {
    //         return response()->json(['message' => 'Đã xảy ra lỗi khi tìm danh sách sinh viên: ' . $e->getMessage()], 500);
    //     }
    // }
    public function getListStudent(Request $request)
    {
        try {
            $selectedClasses = $request->input('selectedClasses', []);
            $nameFilter = $request->input('nameFilter', '');
            $department = $request->input('department', '');

            $query = SinhVien::select('ma_sv', 'ten_sv', 'ngay_sinh', 'phai', 'dia_chi', 'ma_lop');

            if (!empty($department) && empty($selectedClasses)) {
                $query->whereHas('lop', function ($query) use ($department) {
                    $query->where('ma_khoa', $department);
                });
            } else {
                if (!empty($selectedClasses)) {
                    $query->whereIn('ma_lop', $selectedClasses);
                }
            }

            if (!empty($nameFilter)) {
                $query->where('ten_sv', 'like', '%' . $nameFilter . '%')
                    ->orWhere('ma_sv', 'like', '%' . $nameFilter . '%');
            }


            $students = $query->get();


            if ($students->isEmpty()) {
                return response()->json(['message' => 'Không tìm thấy sinh viên.'], 404);
            }

            $students = $students
                ->map(function ($student, $index) {
                    $student->key = $index + 1;
                    $student->ten_lop = $student->lop->ten_lop;
                    $student->ten_khoa = $student->lop->khoa->ten_khoa;
                    $student->phai = ($student->phai == 1 ? 'Nam' : 'Nữ');
                    unset($student->lop);
                    return $student;
                });

            $students = $students->sortBy(function ($student) {
                $names = explode(' ', $student->ten_sv);
                return end($names);
            })->sortBy('ten_lop')->values();

            return response()->json($students);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Đã xảy ra lỗi khi tìm danh sách sinh viên: ' . $e->getMessage()], 500);
        }
    }


    // exportData
    public function exportData(Request $request)
    {
        $data = $request->input('data');
        return Excel::download(new StudentsExport($data), 'students.xlsx');
    }

    public function exportDataDiemDanh(Request $request)
    {
        // $data = $request->input('maGD');
        $data = Khoa::all()->toArray();

        return Excel::download(new DiemDanhExport($data), 'students.xlsx');
    }

    //import data
    public function importData(Request $request)
    {
        $file = $request->file('file');

        try {
            // Đọc nội dung file để kiểm tra dữ liệu
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            $hasValidRow = false;
            foreach ($rows as $row) {

                if (!empty($row[0])) {
                    $hasValidRow = true;
                    break;
                }
            }

            if (!$hasValidRow) {
                return response()->json(['message' => 'File không có dòng dữ liệu hợp lệ'], 400);
            }

            Excel::import(new StudentsImport(), $file);
            return response()->json(['message' => 'Import Thành công'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Lỗi Import Vui lòng thử lại '], 500);
        }
    }



    // Subject
    public function getListSubject(Request $request)
    {
        $ten_mh = $request->ten_mh;

        $query = MonHoc::query();
        if (!empty($ten_mh)) {
            $query->where('ten_mh', 'like', '%' . $ten_mh . '%')
                ->orWhere('ma_mh', 'like', '%' . $ten_mh . '%');
        }
        $subjects = $query->orderBy('ten_mh')->get();

        $newListSubjects = $subjects->map(function ($subject, $index) {
            $subject->stt = $index + 1;
            return $subject;
        });

        return response()->json($newListSubjects);
    }

    // subject save
    public function saveSubject(Request $request, $ma_mh)
    {
        $request->validate(
            [
                'ten_mh' => 'required|string|min:10|max:255',
            ],
            [
                'ten_mh.required' => "Tên môn học không được để trống",
                'ten_mh.max' => "Tên môn học không được dài quá 255 ký tự",
                'ten_mh.min' => "Tên môn học không được ít hơn 10 ký tự",
            ]
        );

        try {
            $existingSubjectByName = MonHoc::where('ten_mh', $request->ten_mh)
                ->where('ma_mh', '!=', $ma_mh)
                ->first();
            if ($existingSubjectByName) {
                return response()->json(['error' => 'Tên môn học đã tồn tại'], 400);
            }
            $ten_mh = $request->ten_mh;
            $result = MonHoc::where('ma_mh', $ma_mh)
                ->update([
                    'ma_mh' => $ma_mh,
                    'ten_mh' => $ten_mh,
                ]);
            if ($result) {
                return response()->json([
                    'message' => 'Cập nhật thành công'
                ], 200);
            }
            return response()->json([
                'error' => 'Cập nhật thất bại'
            ], 400);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteSubject($ma_mh)
    {
        try {
            $deleteSubject = MonHoc::where('ma_mh', $ma_mh)
                ->delete();
            if ($deleteSubject) {
                return response()->json([
                    'message' => "Xóa thành công",
                ], 200);
            }
            return response()->json([
                'error' => "Không thể xóa Vui lòng thử lại",
            ], 400);
        } catch (Exception $e) {
            return response()->json([
                'error' => "Không thể xóa Vui lòng thử lại"
            ], 400);
        }
    }



    // get subject(ma_mh)
    public function getSubject($ma_mh)
    {

        $subject = MonHoc::where('ma_mh', $ma_mh)
            ->first();
        return response()->json($subject);
    }
    public function createSubject(Request $request)
    {
        $ma_mh = $request->ma_mh;
        $ten_mh = $request->ten_mh;
        $so_tiet = $request->so_tiet;

        $validatedData = $request->validate([
            'ma_mh' => 'required|unique:mon_hoc,ma_mh',
            'ten_mh' => 'required|unique:mon_hoc,ten_mh',
        ], [
            'ma_mh.required' => 'Mã môn học là bắt buộc',
            'ma_mh.unique' => 'Mã môn học đã tồn tại',
            'ten_mh.unique' => 'Tên môn học đã tồn tại',
            'ten_mh.required' => 'Tên môn học là bắt buộc',
        ]);
        $subject = MonHoc::create([
            'ma_mh' => $ma_mh,
            'ten_mh' => $ten_mh,
            'so_tiet' => $so_tiet
        ]);
        if ($subject) {
            return response()->json([
                'message' => "Thêm thành công",
            ], 200);
        }
        return response()->json([
            'error' => "Thêm không thành công",
        ], 400);
    }

    // Department
    public function getListDepartment(Request $request)
    {
        $ten_khoa = $request->ten_khoa;

        $query = Khoa::query();
        if (!empty($ten_khoa)) {
            $query->where('ten_khoa', 'like', '%' . $ten_khoa . '%')
                ->orWhere('ma_khoa', 'like', '%' . $ten_khoa . '%');
        }
        $departments = $query->orderBy('ten_khoa')->get();

        $newListDepartments = $departments->map(function ($department, $index) {
            $department->stt = $index + 1;
            return $department;
        });

        return response()->json($newListDepartments);
    }
    // get department(ma_khoa)
    public function getDepartment($ma_khoa)
    {

        $department = Khoa::where('ma_khoa', $ma_khoa)
            ->first();
        return response()->json($department);
    }

    // save
    public function saveDepartment(Request $request, $ma_khoa)
    {

        $request->validate(
            [
                'ten_khoa' => 'required|string|min:5|max:100',
                'ten_khoa' => 'required|unique:khoa,ten_khoa',

            ],
            [
                'ten_khoa.required' => "Tên khoa không được để trống",
                'ten_khoa.max' => "Tên khoa không được dài quá 100 ký tự",
                'ten_khoa.min' => "Tên khoa không được ít hơn 5 ký tự",
                'ten_khoa.unique' => 'Tên khoa đã tồn tại',
            ]
        );
        try {
            $existingDepartmentByName = Khoa::where('ten_khoa', $request->ten_khoa)
                ->where('ma_khoa', '!=', $ma_khoa)
                ->first();
            if ($existingDepartmentByName) {
                return response()->json(['error' => 'Tên khoa đã tồn tại'], 400);
            }
            $ten_khoa = $request->ten_khoa;
            $result = Khoa::where('ma_khoa', $ma_khoa)
                ->update([
                    'ma_khoa' => $ma_khoa,
                    'ten_khoa' => $ten_khoa,
                ]);
            if ($result) {
                return response()->json([
                    'message' => 'Cập nhật thành công'
                ], 200);
            }
            return response()->json([
                'error' => 'Cập nhật thất bại'
            ], 400);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function createDepartment(Request $request)
    {


        $ma_khoa = $request->ma_khoa;
        $ten_khoa = $request->ten_khoa;

        $validatedData = $request->validate([
            'ma_khoa' => 'required|unique:khoa,ma_khoa',
            'ten_khoa' => 'required|unique:khoa,ten_khoa',
        ], [
            'ma_khoa.required' => 'Mã khoa là bắt buộc',
            'ma_khoa.unique' => 'Mã khoa đã tồn tại',
            'ten_khoa.unique' => 'Tên khoa đã tồn tại',
            'ten_khoa.required' => 'Tên khoa là bắt buộc',
        ]);
        $department = Khoa::create([
            'ma_khoa' => $ma_khoa,
            'ten_khoa' => $ten_khoa
        ]);
        if ($department) {
            return response()->json([
                'message' => "Thêm thành công",
            ], 200);
        }
        return response()->json([
            'error' => "Thêm không thành công",
        ], 400);
    }

    public function deleteDepartment($ma_khoa)
    {
        try {
            $deleteDepartment = Khoa::where('ma_khoa', $ma_khoa)
                ->delete();
            if ($deleteDepartment) {
                return response()->json([
                    'message' => "Xóa thành công",
                ], 200);
            }
            return response()->json([
                'error' => "Không thể xóa Vui lòng thử lại",
            ], 400);
        } catch (Exception $e) {
            return response()->json([
                'error' => "Không thể xóa Vui lòng thử lại"
            ], 400);
        }
    }

    // Department
    public function getListClassroom(Request $request)
    {
        $ten_lop = $request->ten_lop;
        $query = Lop::query();
        if (!empty($ten_lop)) {
            $query->where('ten_lop', 'like', '%' . $ten_lop . '%')
                ->orWhere('ma_lop', 'like', '%' . $ten_lop . '%');
        }
        $classrooms = $query->orderBy('ten_lop')->get();

        $newListClassrooms = $classrooms->map(function ($classrooom, $index) {
            $classrooom->stt = $index + 1;
            return $classrooom;
        });

        return response()->json($newListClassrooms);
    }

    public function saveClassroom(Request $request, $ma_lop)
    {

        $validatedData = $request->validate([
            'ten_lop' => [
                'required',
                Rule::unique('lop', 'ten_lop')->ignore($request->ma_lop, 'ma_lop')
            ],
            'sdt_gvcn' => 'nullable|max:11|min:10|regex:/^[0-9]+$/',

        ], [
            'ten_lop.required' => 'Tên lớp là bắt buộc',
            'ten_lop.unique' => 'Tên lớp đã tồn tại',

            'sdt_gvcn.regex' => 'Số điện thoại phải là số',
            'sdt_gvcn.max' => 'Số điện thoại không được vượt quá 11 ký tự',
            'sdt_gvcn.min' => 'Số điện thoại không hợp lệ',
        ]);
        try {
            $ten_lop = $request->ten_lop;
            $gvcn = $request->gvcn;
            $sdt_gvcn = $request->sdt_gvcn;
            $result = Lop::where('ma_lop', $ma_lop)
                ->update([
                    'ma_lop' => $ma_lop,
                    'ten_lop' => $ten_lop,
                    'gvcn' => $gvcn,
                    'sdt_gvcn' => $sdt_gvcn,
                ]);
            if ($result) {
                return response()->json([
                    'message' => 'Cập nhật thành công'
                ], 200);
            }
            return response()->json([
                'error' => 'Cập nhật thất bại'
            ], 400);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function deleteClassroom($ma_lop)
    {
        try {
            $deleteClassroom = Lop::where('ma_lop', $ma_lop)
                ->delete();
            if ($deleteClassroom) {
                return response()->json([
                    'message' => "Xóa thành công",
                ], 200);
            }
            return response()->json([
                'error' => "Không thể xóa Vui lòng thử lại",
            ], 400);
        } catch (Exception $e) {
            return response()->json([
                'error' => "Không thể xóa Vui lòng thử lại"
            ], 400);
        }
    }
    public function createClassroom(Request $request)
    {
        $ma_lop = $request->ma_lop;
        $ten_lop = $request->ten_lop;
        $ma_khoa = $request->ma_khoa;
        $gvcn = $request->gvcn;
        $sdt_gvcn = $request->sdt_gvcn;


        $validatedData = $request->validate([
            'ma_lop' => 'required|unique:lop,ma_lop|min:5',
            'ten_lop' => 'required|unique:lop,ten_lop|min:5',
            'ma_khoa' => 'required|exists:khoa,ma_khoa',
            'sdt_gvcn' => 'nullable|max:11|min:10|regex:/^[0-9]+$/',
        ], [
            'ma_lop.required' => 'Mã lớp là bắt buộc',
            'ma_lop.unique' => 'Mã lớp đã tồn tại',
            'ma_lop.min' => 'Mã lớp không hợp lệ',

            'ten_lop.required' => 'Tên lớp là bắt buộc',
            'ten_lop.unique' => 'Tên lớp đã tồn tại',
            'ten_lop.min' => 'Tên lớp không hợp lệ',

            'ma_khoa.required' => 'Mã khoa là bắt buộc',
            'ma_khoa.exists' => 'Mã khoa không tồn tại',

            'sdt_gvcn.regex' => 'Số điện thoại phải là số',
            'sdt_gvcn.max' => 'Số điện thoại không được vượt quá 11 ký tự',
            'sdt_gvcn.min' => 'Số điện thoại không hợp lệ',
        ]);
        $classroom = Lop::create([
            'ma_lop' => $ma_lop,
            'ten_lop' => $ten_lop,
            'ma_khoa' => $ma_khoa,
            'gvcn' => $gvcn,
            'sdt_gvcn' => $sdt_gvcn,
        ]);
        if ($classroom) {
            return response()->json([
                'message' => "Thêm thành công",
            ], 200);
        }
        return response()->json([
            'error' => "Thêm không thành công",
        ], 400);
    }
    public function getListNameTeachers()
    {
        $teachers = GiaoVien::select('ten_gv')->get();
        return response()->json($teachers);
    }
    public function getClassroom($ma_lop)
    {
        $classroom = Lop::where('ma_lop', $ma_lop)
            ->first();
        return response()->json($classroom);
    }

    //
    public function getListClass()
    {
        $listClass = Lop::select('ma_lop', 'ten_lop')->get();
        if ($listClass) {
            return response()->json([
                'listClass' => $listClass,
            ], 200);
        }
    }

    public function classListStudent(Request $request)
    {
        try {
            $ma_lop = $request->maLop;
            $query = SinhVien::join('lop', 'sinh_vien.ma_lop', 'lop.ma_lop')
                ->where('lop.ma_lop', $ma_lop)
                ->select('ma_sv', 'ten_sv', 'phai', 'email', 'lop.ten_lop')
                ->orderByRaw("SUBSTRING_INDEX(sinh_vien.ten_sv,' ', -1)");
            $sinhViens = $query->get();
            return response()->json($sinhViens, 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
