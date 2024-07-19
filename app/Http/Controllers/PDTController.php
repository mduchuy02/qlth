<?php

namespace App\Http\Controllers;


use App\Exports\StudentsExport;
use App\Imports\StudentsImport;
use App\Models\Khoa;
use App\Models\Lop;
use App\Models\SinhVien;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;

class PDTController extends Controller
{
    public function getAllTeachers()
    {
        $taikhoangv = User::join('giao_vien', 'users.ma_gv', 'giao_vien.ma_gv')
            ->select('users.ma_gv', 'giao_vien.ten_gv as name', 'giao_vien.ngay_sinh', 'giao_vien.phai', 'giao_vien.dia_chi', 'giao_vien.sdt', 'giao_vien.email')
            ->get();

        $taikhoangv = $taikhoangv->map(function ($item, $key) {
            $item->id = $key + 1;
            return $item;
        });
        return response()->json($taikhoangv);
    }
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
    public function getListStudent(Request $request)
    {
        try {
            $selectedClasses = $request->input('selectedClasses', []);
            $nameFilter = $request->input('nameFilter', '');
            $idFilter = $request->input('idFilter', '');

            $query = SinhVien::whereIn('ma_lop', $selectedClasses)
                ->with('lop.khoa')
                ->select('ma_sv', 'ten_sv', 'ngay_sinh', 'ma_lop');
            if (!empty($nameFilter)) {
                $query->where('ten_sv', 'like', '%' . $nameFilter . '%');
            }

            if (!empty($idFilter)) {
                $query->where('ma_sv', 'like', '%' . $idFilter . '%');
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

        try {
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
            'ten_khoa' => 'required',
        ], [
            'ma_khoa.required' => 'Mã khoa là bắt buộc',
            'ma_khoa.unique' => 'Mã khoa đã tồn tại',
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
        try {
            $ten_lop = $request->ten_lop;
            $result = Lop::where('ma_lop', $ma_lop)
                ->update([
                    'ma_lop' => $ma_lop,
                    'ten_lop' => $ten_lop,
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

        $validatedData = $request->validate([
            'ma_lop' => 'required|unique:lop,ma_lop',
            'ten_lop' => 'required',
            'ma_khoa' => 'required|exists:khoa,ma_khoa'
        ], [
            'ma_lop.required' => 'Mã lớp là bắt buộc',
            'ma_lop.unique' => 'Mã lớp đã tồn tại',
            'ten_lop.required' => 'Tên lớp là bắt buộc',
            'ma_khoa.required' => 'Mã khoa là bắt buộc',
            'ma_khoa.exists' => 'Mã khoa không tồn tại'
        ]);
        $classroom = Lop::create([
            'ma_lop' => $ma_lop,
            'ten_lop' => $ten_lop,
            'ma_khoa' => $ma_khoa
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
    // get classroom(ma_lop)
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
}
