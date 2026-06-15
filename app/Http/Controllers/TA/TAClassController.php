<?php

namespace App\Http\Controllers\TA;

use App\Http\Controllers\Controller;
use App\Models\CourseClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TAClassController extends Controller
{
    /**
     * Hiển thị bảng điều khiển danh sách các lớp học được phân công cho TA này
     */
    public function index(Request $request)
    {
        $ta = Auth::user();
        $search = trim($request->input('search', ''));

        // Chỉ lấy những lớp học mà TA này tham gia quản lý (Bảng trung gian class_user)
        $classes = CourseClass::whereHas('users', fn($q) => $q->where('user_id', $ta->id))
            ->with(['course'])
            ->withCount('students') // Đếm số học viên thực tế (role_id = 4)
            ->when($search, function ($query) use ($search) {
                return $query->where('class_name', 'LIKE', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('ta.dashboard', compact('classes'));
    }
}