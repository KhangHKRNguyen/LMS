<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\CourseClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClassroomController extends Controller
{
    public function students($classId)
    {
        $teacher = Auth::user();

        // 1. Lấy lớp học và nạp kèm các buổi học + danh sách bài tập đã giao của buổi đó
        $courseClass = CourseClass::whereHas('users', fn($q) => $q->where('user_id', $teacher->id))
            ->with(['lessonSessions' => function($q) {
                // Eager Load danh sách phân phối bài tập (assignmentDistributions) của buổi học
                $q->orderBy('lesson_date', 'asc')->with('assignmentDistributions'); 
            }])
            ->findOrFail($classId);

        $class = $courseClass;

        // 2. Lấy danh sách học viên trong lớp
        $students = $courseClass->students()->paginate(10);

        // Mốc thời gian hiện tại
        $today = \Carbon\Carbon::today();
        $now = \Carbon\Carbon::now();

        // 3. Xử lý thuật toán ma trận trạng thái nộp bài dựa trên bảng phân phối
        $students->getCollection()->transform(function ($student) use ($courseClass, $today, $now) {
            $totalMissing = 0;
            $sessionStatuses = [];

            foreach ($courseClass->lessonSessions as $session) {
                $sessionDate = \Carbon\Carbon::parse($session->lesson_date);

                // QUY TẮC 1: Buổi học chưa đến ngày -> Không hiện trạng thái (Chưa học)
                if ($sessionDate->isAfter($today)) {
                    $status = 'Chưa đến';
                } 
                else {
                    // Đã đến ngày học hoặc buổi học đã qua
                    $distributions = $session->assignmentDistributions; // Các bài tập được giao cho buổi này

                    // QUY TẮC 2: Buổi đó giáo viên không giao bài tập nào -> Tính là Đủ
                    if ($distributions->isEmpty()) {
                        $status = 'Đủ';
                    } 
                    else {
                        // Buổi đó CÓ bài tập -> Kiểm tra xem có bài nào đã quá hạn đóng link (close_time) hay chưa
                        $hasOverdueAssignment = false;

                        foreach ($distributions as $dist) {
                            // Vì close_time đã được cast là datetime trong Model nên nó là một đối tượng Carbon
                            if ($dist->close_time && $dist->close_time->isPast()) {
                                $hasOverdueAssignment = true;
                                break;
                            }
                        }

                        if ($hasOverdueAssignment) {
                            // QUY TẮC 3: Có bài tập nhưng đã quá hạn nộp bài (close_time nằm trong quá khứ)
                            // [HƯỚNG PHÁT TRIỂN KHI LÀM SUBMISSIONS]:
                            // Sau này khi có bảng submissions, bạn check thêm: 
                            // Nếu $student đã có bản ghi nộp bài khớp với $dist->id -> $status = 'Đủ'
                            // Hiện tại chưa làm phần học viên nộp bài, mặc định quá hạn sẽ tính là 'Thiếu'
                            $status = 'Thiếu';
                            $totalMissing++;
                        } else {
                            // Có bài tập được giao nhưng chưa hết hạn nộp -> Vẫn hiển thị là Đủ (hoặc chờ học viên làm)
                            $status = 'Đủ';
                        }
                    }
                }

                // Ghi nhận trạng thái của buổi học
                $sessionStatuses[$session->id] = $status;
            }

            // Đóng gói thông tin ngược lại vào model student
            $student->session_statuses = $sessionStatuses;
            $student->total_missing = $totalMissing;
            $student->alarm_level = ($totalMissing >= 9) ? 'Mức 2' : (($totalMissing >= 7) ? 'Mức 1' : '—');

            return $student;
        });

        return view('teacher.students.index', compact('class', 'students'));
    }
}