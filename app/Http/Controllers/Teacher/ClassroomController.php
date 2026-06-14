<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\CourseClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClassroomController extends Controller
{
    /**
     * Hiển thị danh sách học viên và ma trận chuyên cần của lớp học
     */
    public function students($classId)
    {
        $teacher = Auth::user();

        // 1. Kiểm tra quyền: Đảm bảo giáo viên này thực sự dạy lớp này
        $courseClass = CourseClass::whereHas('users', fn($q) => $q->where('user_id', $teacher->id))
            ->with(['lessonSessions' => fn($q) => $q->orderBy('id', 'asc')]) // Lấy các buổi học theo thứ tự
            ->findOrFail($classId);

        // 2. Lấy danh sách học viên của lớp (Phân trang 10 học viên/trang)
        $students = $courseClass->students()->paginate(10);

        // 3. Lấy toàn bộ lịch sử điểm danh của lớp để đối chiếu ma trận
        // Nhóm theo user_id và lesson_session_id để dễ truy xuất
        $attendanceMatrix = $courseClass->attendances()
            ->get()
            ->groupBy(['user_id', 'lesson_session_id']);

        // 4. Xử lý logic tính toán cho từng học viên
        $students->getCollection()->transform(function ($student) use ($courseClass, $attendanceMatrix) {
            $totalMissing = 0;
            $sessionStatuses = [];

            // Duyệt qua từng buổi học thực tế của lớp
            foreach ($courseClass->lessonSessions as $session) {
                // Kiểm tra xem học viên có dữ liệu điểm danh cho buổi này không
                $attendance = $attendanceMatrix[$student->id][$session->id] ?? null;
                
                // Giả định: nếu có bản ghi và trạng thái khác 'present' (hoặc tùy cấu trúc DB của bạn)
                // Ở đây kiểm tra nếu không có điểm danh hoặc bản ghi đánh dấu là vắng/thiếu bài
                if (!$attendance || (isset($attendance[0]) && $attendance[0]->status === 'absent')) {
                    $status = 'Thiếu';
                    $totalMissing++;
                } else {
                    $status = 'Đủ';
                }
                
                $sessionStatuses[$session->id] = $status;
            }

            // Tính toán mức độ cảnh báo dựa trên tổng số buổi thiếu bài/vắng
            $alarm = '—';
            if ($totalMissing >= 7) {
                $alarm = 'Mức 2';
            } elseif ($totalMissing >= 3) {
                $alarm = 'Mức 1';
            }

            // Đính kèm các thuộc tính tính toán động vào đối tượng student
            $student->session_statuses = $sessionStatuses;
            $student->total_missing = $totalMissing;
            $student->alarm_level = $alarm;

            return $student;
        });

        // 5. Trả dữ liệu ra ngoài View
        return view('teacher.students.index', compact('courseClass', 'students'));
    }
}