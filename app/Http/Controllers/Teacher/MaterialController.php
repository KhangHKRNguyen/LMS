<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\CourseClass;
use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MaterialController extends Controller
{
    // Thêm tham số $classId vào signature của hàm (mặc định bằng null để tránh lỗi)
    public function index(Request $request, $classId = null)
    {
        $teacher = Auth::user();
        $classes = $teacher->classes;

        $class = null;
        $materials     = collect();
        $search        = $request->get('search', '');
        $filterType    = $request->get('type', '');
        $stats         = [];

        // ĐỒNG BỘ TẠI ĐÂY: Ưu tiên lấy classId từ URL Route, nếu không có mới tìm trong Request query
        $classId = $classId ?? $request->get('class_id');

        if ($classId) {
            $class = CourseClass::where('id', $classId)
                ->whereHas('users', fn($q) => $q->where('user_id', $teacher->id))
                ->firstOrFail();

            // 1. Thống kê tổng số lượng file trước khi lọc danh sách chính
            $allMaterials = $class->materials()->get();
            $stats = [
                'total' => $allMaterials->count(),
                'pdf'   => $allMaterials->filter(fn($m) => strtolower(pathinfo($m->file_path, PATHINFO_EXTENSION)) === 'pdf')->count(),
                'word'  => $allMaterials->filter(fn($m) => in_array(strtolower(pathinfo($m->file_path, PATHINFO_EXTENSION)), ['doc', 'docx']))->count(),
                'ppt'   => $allMaterials->filter(fn($m) => in_array(strtolower(pathinfo($m->file_path, PATHINFO_EXTENSION)), ['ppt', 'pptx']))->count(),
            ];

            // 2. Tạo query lấy danh sách tài liệu và áp dụng bộ lọc tìm kiếm
            $materialsQuery = $class->materials();

            // Lọc theo từ khóa tìm kiếm (title)
            if (!empty($search)) {
                $materialsQuery->where('title', 'LIKE', '%' . $search . '%');
            }

            // Lọc theo định dạng file (loại tài liệu)
            if (!empty($filterType)) {
                if ($filterType === 'word') {
                    $materialsQuery->where(fn($q) => $q->where('file_path', 'LIKE', '%.doc')->orWhere('file_path', 'LIKE', '%.docx'));
                } elseif ($filterType === 'ppt') {
                    $materialsQuery->where(fn($q) => $q->where('file_path', 'LIKE', '%.ppt')->orWhere('file_path', 'LIKE', '%.pptx'));
                } else {
                    $materialsQuery->where('file_path', 'LIKE', '%.' . $filterType);
                }
            }

            $materials = $materialsQuery->get();
        }

        return view('teacher.materials.index', compact(
            'classes', 
            'class', 
            'materials', 
            'stats', 
            'search', 
            'filterType'
        ));
    }

    public function store(Request $request)
    {
        $teacher = Auth::user();

        // 1. Validate dữ liệu đầu vào
        $request->validate([
            'class_id'  => 'required|exists:course_classes,id',
            'title'     => 'required|string|max:255',
            'file'      => 'required|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,zip,rar|max:20480', // tối đa 20MB
        ], [
            'title.required' => 'Vui lòng nhập tiêu đề tài liệu.',
            'file.required'  => 'Vui lòng chọn file tài liệu để tải lên.',
            'file.mimes'     => 'Định dạng file không hợp lệ (Chỉ chấp nhận PDF, Word, Excel, PowerPoint, ZIP, RAR).',
            'file.max'       => 'Dung lượng file không được vượt quá 20MB.',
        ]);

        // 2. Kiểm tra quyền sở hữu lớp học của giảng viên
        $class = CourseClass::whereHas('users', fn($q) => $q->where('user_id', $teacher->id))
            ->findOrFail($request->get('class_id'));

        // 3. Xử lý lưu trữ file vào thư mục public
        $filePath = null;
        if ($request->hasFile('file')) {
            $file     = $request->file('file');
            $filePath = $file->store('uploads/materials', 'public');
        }

        // 4. Tạo bản ghi tài liệu mới vào Database
        Material::create([
            'title'           => $request->get('title'),
            'file_path'       => $filePath,
            'course_class_id' => $class->id,
            'user_id'         => $teacher->id, 
            'file_type'       => $request->file('file')->getClientOriginalExtension(),
        ]);

        // 5. Quay lại kèm thông báo thành công
        return redirect()->route('teacher.materials.index', [
            'class'  => $class->id,
            'search' => $request->get('search', ''),
            'type'   => $request->get('type', ''),
        ])->with('success', 'Tài liệu đã được tải lên thành công!');
    }

    public function download(Material $material)
    {
        $teacher = Auth::user();

        CourseClass::whereHas('users', fn($q) => $q->where('user_id', $teacher->id))
            ->findOrFail($material->course_class_id);

        if (!Storage::disk('public')->exists($material->file_path)) {
            return back()->with('error', 'File không tồn tại trên máy chủ.');
        }

        $fullPath = Storage::disk('public')->path($material->file_path);
        $ext      = pathinfo($material->file_path, PATHINFO_EXTENSION);
        $fileName = $material->title . '.' . $ext;

        return response()->download($fullPath, $fileName);
    }

    public function destroy(Material $material)
    {
        $teacher = Auth::user();

        $class = CourseClass::whereHas('users', fn($q) => $q->where('user_id', $teacher->id))
            ->findOrFail($material->course_class_id);

        Storage::disk('public')->delete($material->file_path);
        $material->delete();

        return redirect()->route('teacher.materials.index', [
            'class' => $class->id,
            'search'   => request('search', ''),
            'type'     => request('type', ''),
        ])->with('success', 'Tài liệu đã được xóa!');
    }
}
