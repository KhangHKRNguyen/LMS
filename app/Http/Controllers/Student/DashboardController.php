<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Lấy thông tin học viên hiện tại kèm theo danh sách lớp học của họ
        $student = Auth::user();

        // Query danh sách lớp học thuộc về học viên này
        // Sử dụng quan hệ 'users' nhưng lọc vai trò hoặc gọi qua quan hệ nếu bạn định nghĩa ngược lại
        // Ở đây dựa vào Model CourseClass có hàm students(), ta suy ra User sẽ có quan hệ belongsToMany tới CourseClass.
        // Giả sử mối quan hệ đó trong Model User tên là 'courseClasses' hoặc 'classes'
        
        $classes = $student->belongsToMany(\App\Models::class, 'class_user', 'user_id', 'course_class_id')
            ->with('course') // Load thông tin khóa học tương ứng
            ->withCount(['users as students_count' => function ($query) {
                $query->where('role', 'student'); // Đếm số lượng học viên trong lớp
            }])
            ->get();

        // Lấy ngày tháng hiện tại hiển thị theo tiếng Việt giống như bản thiết kế UI
        Carbon::setLocale('vi');
        $currentDateString = "Hôm nay là " . Carbon::now()->isoFormat('dddd, [ngày] D [tháng] M [năm] Y');

        return view('student.dashboard', [
            'student' => $student,
            'classes' => $classes,
            'currentDateString' => $currentDateString
        ]);
    }
}