<?php

namespace App\Http\Controllers\TA;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\CourseClass;
use App\Models\LessonSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Hiển thị bảng ma trận điểm danh chi tiết của lớp kèm logic kiểm tra thời hạn
     */
    public function classAttendance($classId)
    {
        $ta = Auth::user();

        // 1. Kiểm tra quyền sở hữu lớp học của TA và nạp dữ liệu liên quan
        $class = CourseClass::where('id', $classId)
            ->whereHas('users', fn($q) => $q->where('user_id', $ta->id))
            ->with([
                'course', 
                'students', 
                'lessonSessions' => function($q) {
                    $q->orderBy('lesson_date', 'asc')->orderBy('id', 'asc');
                }
            ])->firstOrFail();

        $students = $class->students;
        $lessonSessions = $class->lessonSessions;
        $today = Carbon::today();

        // 2. Tính toán khoảng ngày hợp lệ cho phép điểm danh của từng buổi học
        foreach ($lessonSessions as $index => $session) {
            $sessionDate = Carbon::parse($session->lesson_date)->startOfDay();
            
            // Ngày bắt đầu: Từ chính ngày học đó
            $startValid = $sessionDate->copy();
            
            // Ngày kết thúc: Ngay trước ngày của buổi kế tiếp. Nếu là buổi cuối, lấy theo ngày kết thúc lớp học hoặc vô hạn.
            if (isset($lessonSessions[$index + 1])) {
                $nextSessionDate = Carbon::parse($lessonSessions[$index + 1]->lesson_date)->startOfDay();
                $endValid = $nextSessionDate->copy()->subDay(); // Đến hết ngày hôm trước của buổi sau
            } else {
                // Buổi học cuối cùng: cho phép điểm danh từ ngày học đó trở đi (hoặc đến ngày kết thúc lớp học)
                $endValid = $class->end_date ? Carbon::parse($class->end_date)->endOfDay() : null;
            }

            $session->start_valid_date = $startValid;
            $session->end_valid_date = $endValid;

            // Kiểm tra xem ngày hôm nay có nằm trong ngưỡng được phép điểm danh không
            $isEditable = true;
            if ($today->lt($startValid)) {
                $isEditable = false; // Chưa tới ngày học
            }
            if ($endValid && $today->gt($endValid)) {
                $isEditable = false; // Quá ngày của buổi tiếp theo (Đã khóa)
            }

            $session->is_editable = $isEditable;
        }

        // 3. Gom dữ liệu điểm danh về mảng tối ưu dạng $attendanceMatrix[student_id][session_id]
        $attendanceMatrix = [];
        $sessionIds = $lessonSessions->pluck('id')->toArray();

        if (!empty($sessionIds)) {
            $attendances = Attendance::whereIn('lesson_session_id', $sessionIds)->get();
            foreach ($attendances as $attendance) {
                $attendanceMatrix[$attendance->user_id][$attendance->lesson_session_id] = $attendance->status;
            }
        }

        return view('ta.classes.attendance', compact('class', 'students', 'lessonSessions', 'attendanceMatrix', 'today'));
    }

    /**
     * Lưu hoặc cập nhật dữ liệu điểm danh hàng loạt từ form gửi lên (Có bảo vệ backend)
     */
    public function storeMatrix(Request $request, $classId)
    {
        $request->validate([
            'attendance' => 'required|array',
        ]);

        $ta = Auth::user();
        $class = CourseClass::whereHas('users', fn($q) => $q->where('user_id', $ta->id))->findOrFail($classId);
        
        $today = Carbon::today();
        $lessonSessions = $class->lessonSessions()->orderBy('lesson_date', 'asc')->orderBy('id', 'asc')->get();

        // Tạo mảng tra cứu nhanh trạng thái chỉnh sửa của các buổi học bảo mật trên Backend
        $editableSessions = [];
        foreach ($lessonSessions as $index => $session) {
            $startValid = Carbon::parse($session->lesson_date)->startOfDay();
            if (isset($lessonSessions[$index + 1])) {
                $endValid = Carbon::parse($lessonSessions[$index + 1]->lesson_date)->startOfDay()->subDay();
            } else {
                $endValid = $class->end_date ? Carbon::parse($class->end_date)->endOfDay() : null;
            }

            $isEditable = true;
            if ($today->lt($startValid) || ($endValid && $today->gt($endValid))) {
                $isEditable = false;
            }
            $editableSessions[$session->id] = $isEditable;
        }

        // Duyệt mảng dữ liệu gửi lên
        foreach ($request->attendance as $sessionId => $studentStatuses) {
            // Nếu buổi học này không nằm trong thời gian được phép chỉnh sửa thì bỏ qua không lưu
            if (!isset($editableSessions[$sessionId]) || !$editableSessions[$sessionId]) {
                continue;
            }

            foreach ($studentStatuses as $studentId => $status) {
                Attendance::updateOrCreate(
                    [
                        'lesson_session_id' => $sessionId,
                        'user_id'           => $studentId,
                    ],
                    ['status' => $status]
                );
            }

            // Đánh dấu buổi học này đã được xử lý điểm danh vào DB
            LessonSession::where('id', $sessionId)->update(['attendance_status' => 'đã điểm danh']);
        }

        return redirect()->back()->with('success', 'Xác nhận và cập nhật dữ liệu điểm danh thành công!');
    }
}