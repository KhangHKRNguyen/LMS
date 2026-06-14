<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    // Hiển thị danh sách khóa học
    public function index(Request $request)
    {
        $search = trim($request->input('search', ''));

        $courses = Course::withCount('courseClasses')
            ->when($search, function ($query) use ($search) {
                return $query->where('name', 'like', "%{$search}%")
                             ->orWhere('description', 'like', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.courses.index', compact('courses'));
    }

    // [BỔ SUNG] Hiển thị form tạo mới khóa học
    public function create()
    {
        return view('admin.courses.create');
    }

    // [BỔ SUNG] Xử lý lưu khóa học mới vào cơ sở dữ liệu
    public function store(Request $request)
    {
        // Kiểm tra tính hợp lệ của dữ liệu form gửi lên
        $request->validate([
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'output_target' => 'nullable|string|max:255',
            'duration'      => 'nullable|string|max:255',
        ], [
            'name.required' => 'Vui lòng nhập tên khóa học.',
            'name.max'      => 'Tên khóa học không được vượt quá 255 ký tự.',
            'output_target.max' => 'Chuẩn đầu ra không được vượt quá 255 ký tự.',
            'duration.max'  => 'Thời lượng không được vượt quá 255 ký tự.',
        ]);

        // Tạo khóa học mới (Model Course đã có $fillable sẵn)
        Course::create($request->all());

        // Điều hướng kèm thông báo thành công
        return redirect()->route('admin.courses.index')->with('success', 'Thêm khóa học mới thành công!');
    }

    // Hiển thị chi tiết khóa học
    public function show(Course $course)
    {
        return redirect()->route('admin.classes.index', ['course_id' => $course->id]);
    }

    // Hiển thị form chỉnh sửa khóa học
    public function edit(Course $course)
    {
        return view('admin.courses.edit', compact('course'));
    }

    // Cập nhật dữ liệu khóa học
    public function update(Request $request, Course $course)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'output_target' => 'nullable|string|max:255',
            'duration'      => 'nullable|string|max:255',
        ], [
            'name.required' => 'Vui lòng nhập tên khóa học.',
            'name.max'      => 'Tên khóa học không được vượt quá 255 ký tự.',
            'output_target.max' => 'Chuẩn đầu ra không được vượt quá 255 ký tự.',
            'duration.max'  => 'Thời lượng không được vượt quá 255 ký tự.',
        ]);

        // Tiến hành lưu thông tin thay đổi vào Database
        $course->update($request->all());

        return redirect()->route('admin.courses.index')->with('success', 'Cập nhật thông tin khóa học thành công!');
    }

    // Xóa khóa học
    public function destroy(Course $course)
    {
        if ($course->courseClasses()->exists()) {
            return redirect()->route('admin.courses.index')->with('error', 'Không thể xóa khóa học này vì đang tồn tại lớp học thuộc cấu trúc quản lý!');
        }

        $course->delete();

        return redirect()->route('admin.courses.index')->with('success', 'Xóa khóa học thành công!');
    }
}