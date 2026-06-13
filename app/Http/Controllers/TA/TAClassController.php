<?php

namespace App\Http\Controllers\TA;

use App\Http\Controllers\Controller;
use App\Models\CourseClass;
use Illuminate\Http\Request;

class TAClassController extends Controller
{
    /**
     * Hiển thị danh sách lớp học phụ trách
     */
    public function index(Request $request)
    {
        // Sử dụng eager loading để lấy thông tin khóa học và đếm số học viên tối ưu
        $query = CourseClass::with('course')->withCount('students');

        // Tìm kiếm theo tên hoặc mã lớp học
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('class_name', 'like', "%{$search}%")
                  ->orWhere('id', $search);
            });
        }

        // Lọc theo trạng thái lớp học (active / ended)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Phân trang 10 dòng mỗi trang
        $classes = $query->paginate(10);

        return view('ta.classes.index', compact('classes'));
    }

    /**
     * Vào trang chi tiết điểm danh của lớp học cụ thể
     */
    public function attendance($id)
    {
        // Lấy chi tiết lớp kèm danh sách học viên trong bảng quan hệ N-N đã có ở Model
        $class = CourseClass::with(['course', 'students'])->findOrFail($id);

        return view('ta.classes.attendance', compact('class'));
    }
}