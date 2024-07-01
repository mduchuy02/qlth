<?php

namespace App\Http\Controllers;


use App\Exports\StudentsExport;
use App\Imports\StudentsImport;
use App\Models\Khoa;
use App\Models\Lop;
use App\Models\SinhVien;
use App\Models\User;
use Illuminate\Http\Request;
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
        $students = $query
            ->orderBy('ten_sv')
            ->get()
            ->map(function ($student, $index) {
                $student->key = $index + 1;
                $student->ten_lop = $student->lop->ten_lop;
                $student->ten_khoa = $student->lop->khoa->ten_khoa;
                unset($student->lop);
                return $student;
            });
        return response()->json($students);
    }

    // exportData
    public function exportData(Request $request)
    {
        $data = $request->input('data');
        return Excel::download(new StudentsExport($data), 'students.xlsx');
    }

    public function importData(Request $request)
    {
        $file = $request->file('file');
        try {
            Excel::import(new StudentsImport(), $file);

            return response()->json(['message' => 'Imported successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to import: ' . $e->getMessage()], 500);
        }
    }
}
