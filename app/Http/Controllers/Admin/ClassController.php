<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseClass;
use App\Models\User;
use App\Services\ClassMemberImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClassController extends Controller
{
    protected $importService;

    // Inject Service xử lý file thành viên lớp học vào Controller
    public function __construct(ClassMemberImportService $importService)
    {
        $this->importService = $importService;
    }

    /**
     * TÍNH NĂNG 1: QUẢN LÝ THÔNG TIN LỚP HỌC (CRUD)
     */

    // GET /admin/classes - Danh sách lớp học (Có tìm kiếm & phân trang)
    public function index(Request $request)
    {
        // Loại bỏ hoàn toàn khoảng trắng thừa ở hai đầu đầu chuỗi tìm kiếm
        $search = trim($request->input('search', ''));

        $classes = CourseClass::with('course')
            ->withCount(['users', 'assignments']) 
            ->when($search, function ($query) use ($search) {
                // Gom tất cả điều kiện OR vào trong một nhóm WHERE duy nhất bằng Closure
                return $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('class_name', 'like', "%{$search}%")
                            ->orWhere('room', 'like', "%{$search}%")
                            ->orWhereHas('course', function ($q) use ($search) {
                                $q->where('name', 'like', "%{$search}%");
                            });
                });
            })
            ->latest()
            ->paginate(10);
        return view('admin.classes.index', compact('classes'));
    }

    // GET /admin/classes/create - Giao diện tạo lớp học mới
    public function create()
    {
        $courses = Course::all(); // Lấy danh sách khóa học để admin chọn lựa dropdown
        return view('admin.classes.create', compact('courses'));
    }

    // POST /admin/classes - Lưu thông tin lớp học mới
    public function store(Request $request)
    {
        $validated = $request->validate([
            'class_name' => 'required|string|max:255',
            'course_id'  => 'required|exists:courses,id',
            'room'       => 'nullable|string|max:255',
            'start_time' => 'required|date',
            'end_time'   => 'required|date|after_or_equal:start_time',
            'status'     => 'required|string|in:Đang mở,Đã đóng',
        ]);

        CourseClass::create($validated);

        return redirect()->route('admin.classes.index')->with('success', 'Tạo lớp học mới thành công!');
    }

    // GET /admin/classes/{id}/edit - Giao diện chỉnh sửa lớp học
    public function edit($id)
    {
        $class = CourseClass::findOrFail($id);
        $courses = Course::all();
        
        return view('admin.classes.edit', compact('class', 'courses'));
    }

    // PUT /admin/classes/{id} - Cập nhật thông tin lớp học
    public function update(Request $request, $id)
    {
        $class = CourseClass::findOrFail($id);

        $validated = $request->validate([
            'class_name' => 'required|string|max:255',
            'course_id'  => 'required|exists:courses,id',
            'room'       => 'nullable|string|max:255',
            'start_time' => 'required|date',
            'end_time'   => 'required|date|after_or_equal:start_time',
            'status'     => 'required|string|in:Đang mở,Đã đóng',
        ]);

        $class->update($validated);

        return redirect()->route('admin.classes.index')->with('success', 'Cập nhật thông tin lớp học thành công!');
    }

    // DELETE /admin/classes/{id} - Xóa lớp học
    public function destroy($id)
    {
        $class = CourseClass::findOrFail($id);

        // Kiểm tra điều kiện có cho phép xóa hay không dựa trên hàm Helper trong model CourseClass của bạn
        if (method_exists($class, 'isDeletable') && !$class->isDeletable()) {
            return redirect()->route('admin.classes.index')->with('error', 'Không thể xóa lớp học do đã phát sinh học viên hoặc bài tập!');
        }

        $class->delete();
        return redirect()->route('admin.classes.index')->with('success', 'Xóa lớp học thành công!');
    }


    /**
     * TÍNH NĂNG 2: QUẢN LÝ THÀNH VIÊN TRONG LỚP (LẺ & FILE HÀNG LOẠT)
     */

    // GET & POST /admin/classes/{id}/members - Giao diện quản lý thành viên chung (Xem list + Đọc file Preview)
    public function members(Request $request, $id)
    {
        $class = CourseClass::findOrFail($id);
        
        // Lấy danh sách thành viên hiện tại của lớp học
        $currentMembers = $class->users()->get(); 
        $previewMembers = null;

        // Nếu admin thực hiện upload file để xem trước (Preview)
        if ($request->hasFile('import_file')) {
            $request->validate([
                'import_file' => 'required|file|mimes:csv,txt,xlsx|max:5120',
            ]);

            try {
                // Gọi service xử lý phân tích dữ liệu file import
                $previewMembers = $this->importService->importMembers($request->file('import_file'));
            } catch (\Exception $e) {
                return back()->with('error', 'Lỗi phân tích file: ' . $e->getMessage());
            }
        }

        return view('admin.classes.members', compact('class', 'currentMembers', 'previewMembers'));
    }

    // POST /admin/classes/{id}/members/add-single - Thêm thủ công lẻ 1 thành viên theo Mã số (id)
    public function addMember(Request $request, $id)
    {
        $class = CourseClass::findOrFail($id);

        $request->validate([
            'user_code' => 'required|string', // Mã sinh viên/giảng viên nhập từ form lẻ
        ]);

        $userCode = $request->input('user_code');
        $user = User::find($userCode);

        if (!$user) {
            return back()->with('error', "Không tìm thấy tài khoản nào có mã định danh: {$userCode}");
        }

        // Kiểm tra xem tài khoản này đã được gán vào lớp này từ trước chưa
        if ($class->users()->where('user_id', $user->id)->exists()) {
            return back()->with('error', 'Thành viên này hiện đã có mặt trong lớp học!');
        }

        // Đính kèm user vào bảng trung gian (class_user)
        $class->users()->attach($user->id);

        return back()->with('success', "Đã thêm thành công thành viên: {$user->name} vào lớp.");
    }

    // DELETE /admin/classes/{class_id}/members/{user_id} - Xóa thành viên khỏi lớp
    public function removeMember($classId, $userId)
    {
        $class = CourseClass::findOrFail($classId);
        
        // Gỡ bỏ liên kết trong bảng trung gian
        $class->users()->detach($userId);

        return back()->with('success', 'Đã xóa thành viên khỏi lớp học thành open.');
    }

    // POST /admin/classes/{id}/members/store-bulk - Lưu hàng loạt thành viên từ dữ liệu file preview vào DB
    public function storeBulkMembers(Request $request, $id)
    {
        $class = CourseClass::findOrFail($id);
        
        // Phục hồi lại chuỗi JSON được đẩy lên từ input hidden của form xác nhận
        $verifiedData = $request->input('verified_data');
        
        if (!$verifiedData) {
            return back()->with('error', 'Không tìm thấy dữ liệu xem trước hợp lệ để lưu!');
        }

        $membersArray = json_decode($verifiedData, true);

        if (!is_array($membersArray)) {
            return back()->with('error', 'Định dạng dữ liệu xác nhận không chính xác!');
        }

        DB::beginTransaction();
        try {
            $successCount = 0;
            foreach ($membersArray as $memberData) {
                // Chỉ xử lý các bản ghi được Service đánh dấu hợp lệ (có id tài khoản thực tế sống trong hệ thống)
                if (isset($memberData['id'])) {
                    $user = User::find($memberData['id']);
                    
                    if ($user && !$class->users()->where('user_id', $user->id)->exists()) {
                        $class->users()->attach($user->id);
                        $successCount++;
                    }
                }
            }
            DB::commit();

            return redirect()->route('admin.classes.members', $class->id)
                ->with('success', "Đã nạp thành công hàng loạt {$successCount} thành viên vào lớp học!");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Hệ thống gặp lỗi khi lưu dữ liệu hàng loạt: ' . $e->getMessage());
        }
    }
}