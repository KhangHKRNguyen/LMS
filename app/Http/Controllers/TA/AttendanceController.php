<?php

namespace App\Http\Controllers\TA;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\CourseClass;
use App\Models\LeaveRequest;
use App\Models\LessonSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    /**
     * Hiển thị bảng ma trận điểm danh chi tiết của lớp (image_c58470.png)
     */
    public function classAttendance($classId)
    {
        $ta = Auth::user();

        // 1. Kiểm tra quyền của TA và Eager Load học viên + các buổi học sắp xếp theo thứ tự
        $class = CourseClass::where('id', $classId)
            ->whereHas('users', fn($q) => $q->where('user_id', $ta->id))
            ->with([
                'course', 
                'students', 
                'lessonSessions' => function($q) {
                    $q->orderBy('session_date', 'asc')->orderBy('id', 'asc');
                }
            ])->firstOrFail();

        $students = $class->students;
        $lessonSessions = $class->lessonSessions;

        // 2. Gom dữ liệu điểm danh về dạng ma trận $attendanceMatrix[student_id][session_id] để tối ưu query
        $attendanceMatrix = [];
        $sessionIds = $lessonSessions->pluck('id')->toArray();

        if (!empty($sessionIds)) {
            $attendances = Attendance::whereIn('lesson_session_id', $sessionIds)->get();
            foreach ($attendances as $attendance) {
                $attendanceMatrix[$attendance->user_id][$attendance->lesson_session_id] = $attendance->status;
            }
        }

        return view('ta.classes.attendance', compact('class', 'students', 'lessonSessions', 'attendanceMatrix'));
    }

    /**
     * Lưu/Cập nhật dữ liệu điểm danh hàng loạt từ giao diện ma trận gửi lên
     */
    public function storeMatrix(Request $request, $classId)
    {
        $request->validate([
            'attendance' => 'required|array',
        ]);

        $ta = Auth::user();
        // Xác thực lại quyền sở hữu lớp học của TA để bảo mật
        $class = CourseClass::whereHas('users', fn($q) => $q->where('user_id', $ta->id))->findOrFail($classId);

        // Duyệt mảng dữ liệu cấu trúc: attendance[session_id][student_id] = 'present'/'absent'
        foreach ($request->attendance as $sessionId => $studentStatuses) {
            foreach ($studentStatuses as $studentId => $status) {
                Attendance::updateOrCreate(
                    [
                        'lesson_session_id' => $sessionId,
                        'user_id'           => $studentId,
                    ],
                    ['status' => $status]
                );
            }

            // Đánh dấu buổi học này đã được thực hiện điểm danh
            LessonSession::where('id', $sessionId)->update(['attendance_status' => 'đã điểm danh']);
        }

        return redirect()->back()->with('success', 'Xác nhận và cập nhật dữ liệu điểm danh thành công!');
    }
}