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
    public function show($class)
    {
        // Eager load mối quan hệ 'course' và 'users' 
        $class = CourseClass::with([
            'course', 
            'users' => function($query) {
                // SỬA: Lọc dựa trên cột database thực tế (role_id khác 4 để loại bỏ học viên)
                $query->where('users.role_id', '!=', 4); 
            }
        ])->findOrFail($class);

        return view('teacher.classes.info', compact('class'));
    }
}