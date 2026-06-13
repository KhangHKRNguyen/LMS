<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\CourseClass;
use Illuminate\Http\Request;

class ClassWorkspaceController extends Controller
{
    /**
     * Hiển thị thông tin chi tiết của một lớp học (Tổng quan)
     */
    public function show($id)
    {
        // Eager load mối quan hệ 'course' và 'users' (chỉ lấy giảng viên/trợ lý, bỏ học viên qua một bên)
        $class = CourseClass::with([
            'course', 
            'users' => function($query) {
                $query->where('role', '!=', 'student'); // Lấy tất cả nhân sự không phải học viên
            }
        ])->findOrFail($id);

        return view('teacher.classes.show', compact('class'));
    }
}