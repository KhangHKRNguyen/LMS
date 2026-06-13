<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\CourseClass;
use Illuminate\Support\Facades\Auth;

class ClassController extends Controller
{
    public function index()
    {
        // 1. Lấy danh sách lớp của riêng giảng viên đang đăng nhập
        $classes = CourseClass::whereHas('users', function ($query) {
            $query->where('user_id', Auth::id());
        })
        ->with(['course'])       // Nạp kèm thông tin khóa học
        ->withCount('students')  // Tự động đếm sĩ số học viên trong lớp
        ->get();

        // 2. Trả dữ liệu ra view dashboard của teacher
        return view('teacher.dashboard', compact('classes'));
    }
}