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

    // GET /admin/classes - Danh sách lớp học
    public function index(Request $request)
    {
        $search = trim($request->input('search', ''));
        $courseId = $request->input('course_id');

        // Bắt buộc Eager Load 'course' và 'teacher' để tránh N+1 Query và chống lỗi rỗng
        $classes = CourseClass::with(['course', 'teacher'])
            ->withCount([
                'students' // Tự động đếm số lượng học viên -> sinh ra biến students_count
            ])
            ->when($courseId, function ($query) use ($courseId) {
                return $query->where('course_id', $courseId);
            })
            ->when($search, function ($query) use ($search) {
                return $query->where('class_name', 'LIKE', "%{$search}%");
            })
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('admin.classes.index', compact('classes', 'search'));
    }

    // GET /admin/classes/create - Giao diện tạo lớp học mới
    public function create()
    {
        $courses = Course::all(); // Lấy danh sách khóa học để admin chọn lựa dropdown
        return view('admin.classes.create', compact('courses'));
    }

    // POST /admin/classes - Lưu thông tin lớp học & tự động tính ngày kết thúc (Thời lượng chia 2)
    public function store(Request $request)
    {
        // 1. Kiểm tra dữ liệu đầu vào
        $validated = $request->validate([
            'class_name'     => 'required|string|max:255',
            'start_time'     => 'required|date|after_or_equal:today',
            'room'           => 'nullable|string|max:255',
            'course_id'      => 'required|exists:courses,id',
            'status'         => 'required|string',
            'days_of_week'   => 'required|array|min:1', 
            'days_of_week.*' => 'integer|between:1,6',   
        ], [
            'class_name.required'   => 'Tên lớp học không được bỏ trống.',
            'start_time.required'   => 'Ngày bắt đầu không được bỏ trống.',
            'start_time.after_or_equal' => 'Ngày bắt đầu không được là một ngày trong quá khứ.',
            'days_of_week.required' => 'Vui lòng chọn ít nhất một ngày học định kỳ trong tuần.',
        ]);

        // 2. Lấy thông tin khóa học để tính số buổi học dựa trên số giờ
        $course = Course::findOrFail($validated['course_id']);
        
        $totalHours = intval($course->duration); // Bóc tách số giờ (VD: "60 giờ" -> 60)
        
        // CÔNG THỨC: Số buổi = Tổng số giờ / 2 (Mỗi buổi học kéo dài 2 giờ)
        // Dùng ceil() để làm tròn lên nếu tổng số giờ bị lẻ (VD: 45 giờ / 2 = 22.5 -> 23 buổi)
        $totalSessions = ceil($totalHours / 2); 

        if ($totalSessions <= 0) {
            return back()->with('error', 'Khóa học này chưa có tổng số giờ (duration) hợp lệ để tự động tính số buổi học.')->withInput();
        }

        DB::beginTransaction();

        try {
            // 3. Thuật toán tự động sinh chuỗi ngày học dựa trên lịch chọn tuần
            $selectedDays = $validated['days_of_week']; 
            $currentDate = \Carbon\Carbon::parse($validated['start_time']);
            $sessionDates = [];

            // Chạy vòng lặp tìm ngày khớp lịch cho đến khi gom đủ số lượng buổi học đã chia 2
            while (count($sessionDates) < $totalSessions) {
                // dayOfWeekIso: 1 (Thứ 2) -> 7 (Chủ nhật)
                if (in_array($currentDate->dayOfWeekIso, $selectedDays)) {
                    $sessionDates[] = $currentDate->format('Y-m-d');
                }
                $currentDate->addDay();
            }

            // Ngày học cuối cùng trong mảng chính là ngày kết thúc lớp học
            $calculatedEndDate = end($sessionDates);

            // 4. Tạo mới lớp học với ngày kết thúc tự động tính toán được
            $class = CourseClass::create([
                'class_name' => $validated['class_name'],
                'start_date' => $validated['start_time'],
                'end_date'   => $calculatedEndDate, 
                'room'       => $validated['room'] ?? null,
                'course_id'  => $validated['course_id'],
                'status'     => $validated['status'],
            ]);

            // 5. Thêm hàng loạt các buổi học tương ứng vào bảng lesson_sessions
            foreach ($sessionDates as $date) {
                $class->lessonSessions()->create([
                    'lesson_date' => $date,
                ]);
            }

            DB::commit();

            return redirect()->route('admin.classes.index')
                ->with('success', "Tạo lớp học thành công! Khóa học gồm {$totalHours} giờ đã được tự động quy đổi thành {$totalSessions} buổi học. Ngày kết thúc dự kiến: " . \Carbon\Carbon::parse($calculatedEndDate)->format('d/m/Y'));

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Hệ thống gặp sự cố khi xử lý lịch học: ' . $e->getMessage())->withInput();
        }
    }

    // GET /admin/classes/{id}/edit - Giao diện chỉnh sửa lớp học
    public function edit($id)
    {
        $class = CourseClass::findOrFail($id);
        $courses = Course::all();
        
        return view('admin.classes.edit', compact('class', 'courses'));
    }

    // PUT /admin/classes/{id} - Cập nhật thông tin lớp học
    public function update(Request $request, CourseClass $class)
    {
        // Chỉ validate những trường được phép sửa đổi công khai
        $validated = $request->validate([
            'class_name' => 'required|string|max:255',
            'room'       => 'nullable|string|max:255',
            'status'     => 'required|string',
        ], [
            'class_name.required' => 'Tên lớp học không được bỏ trống.',
        ]);

        // Tiến hành cập nhật, tuyệt đối không truyền start_date, end_date hay course_id vào đây
        $class->update([
            'class_name' => $validated['class_name'],
            'room'       => $validated['room'] ?? null,
            'status'     => $validated['status'],
        ]);

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
        $search = trim($request->input('search', ''));
        
        // Lọc thành viên theo search term
        $members = $class->users()
            ->with('roleRelation')
            ->when($search, function ($query) use ($search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('users.id', 'LIKE', "%{$search}%")
                    ->orWhere('users.name', 'LIKE', "%{$search}%")
                    ->orWhere('users.email', 'LIKE', "%{$search}%");
                });
            })
            ->get();
        
        $previewMembers = null;

        // Nếu admin thực hiện upload file để xem trước (Preview)
        if ($request->hasFile('import_file')) {
            $request->validate([
                'import_file' => 'required|file|mimes:csv,txt,xlsx|max:5120',
            ]);

            try {
                $previewMembers = $this->importService->importMembers($request->file('import_file'));
            } catch (\Exception $e) {
                return back()->with('error', 'Lỗi phân tích file: ' . $e->getMessage());
            }
        }

        return view('admin.classes.members', compact('class', 'members', 'search', 'previewMembers'));
    }

    // POST /admin/classes/{id}/members/add-single - Thêm thủ công lẻ 1 thành viên theo Mã số (id)
    public function addSingleMember(Request $request, CourseClass $class)
    {
        // Lấy giá trị định danh từ form (hỗ trợ linh hoạt các thuộc tính name có thể đặt ở View)
        $identifier = trim($request->input('member_code') ?? $request->input('user_id') ?? $request->input('email') ?? $request->input('single_member_code') ?? '');

        // Thuật toán dự phòng: Quét lấy tham số đầu tiên khác token nếu view đặt name lạ
        if (empty($identifier)) {
            foreach ($request->all() as $key => $value) {
                if ($key !== '_token' && !empty($value) && is_string($value)) {
                    $identifier = trim($value);
                    break;
                }
            }
        }

        if (empty($identifier)) {
            return back()->with('error', 'Vui lòng nhập thông tin thành viên muốn thêm!');
        }

        // Tìm kiếm tài khoản thông minh qua 3 trường định danh phổ biến
        $user = User::where('id', $identifier)
                    ->orWhere('email', $identifier)
                    ->orWhere('phone', $identifier)
                    ->first();

        if (!$user) {
            return back()->with('error', "Không tìm thấy thành viên nào khớp với thông tin: \"{$identifier}\"");
        }

        // Kiểm tra xem thành viên này đã có sẵn trong lớp học chưa
        if ($class->users()->where('user_id', $user->id)->exists()) {
            return back()->with('error', "Thành viên \"{$user->name}\" đã tham gia lớp học này từ trước!");
        }

        // Đính kèm bản ghi vào bảng trung gian class_user
        $class->users()->attach($user->id);

        return redirect()->route('admin.classes.members', $class->id)
            ->with('success', "Đã thêm thành viên \"{$user->name}\" vào lớp học thành công!");
    }

    // DELETE /admin/classes/{class_id}/members/{user_id} - Xóa thành viên khỏi lớp
    public function removeMember($classId, $userId)
    {
        $class = CourseClass::findOrFail($classId);
        
        // Gỡ bỏ liên kết trong bảng trung gian
        $class->users()->detach($userId);

        return back()->with('success', 'Đã xóa thành viên khỏi lớp học thành công!');
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

    public function previewMembers(Request $request, CourseClass $class)
    {
        // Kiểm tra xem request có chứa file nạp không, ví dụ tên file input là 'import_file'
        if (!$request->hasFile('import_file')) {
            return back()->with('error', 'Vui lòng chọn file dữ liệu!');
        }

        // Gọi đúng tên hàm importMembers và truyền file vào
        $previewMembers = $this->importService->importMembers($request->file('import_file')); 

        return view('admin.classes.members', compact('class', 'previewMembers'));
    }

    public function downloadSample()
    {
        // Lấy nội dung file CSV mẫu đã được viết sẵn trong Service của bạn
        $content = $this->importService->sampleCsvContent();

        // Trả về file cho trình duyệt tự động tải xuống
        return response($content)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="mau_nap_thanh_vien.csv"');
    }

    public function searchUsers(Request $request, CourseClass $class)
    {
        $search = trim($request->input('q', ''));

        // Nếu người dùng gõ ít hơn 2 ký tự thì không cần tìm kiếm để đỡ tốn tài nguyên
        if (strlen($search) < 2) {
            return response()->json([]);
        }

        // Lấy danh sách ID của những người ĐÃ Ở TRONG LỚP để loại trừ
        $excludedUserIds = $class->users()->pluck('users.id')->toArray();

        // Tìm kiếm trong bảng users
        $users = User::whereNotIn('id', $excludedUserIds)
            ->where(function ($query) use ($search) {
                $query->where('id', 'LIKE', "%{$search}%")
                    ->orWhere('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%");
            })
            ->limit(8) // Chỉ lấy tối đa 8 kết quả để hiển thị đẹp gọn
            ->get(['id', 'name', 'email']);

        // Trả về định dạng JSON cho Javascript xử lý công khai
        return response()->json($users);
    }
}