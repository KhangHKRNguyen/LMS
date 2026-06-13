<?php

namespace App\Http\Controllers\TA;

use App\Http\Controllers\Controller;
use App\Models\CourseClass;
use App\Models\Submission;
use App\Models\Attendance;
use Illuminate\Http\Request;

class ClassroomSummaryController extends Controller
{
    public function summary($classId)
    {
        // 1. Lấy thông tin lớp học cùng thông tin khóa học liên kết
        $courseClass = CourseClass::with('course')->findOrFail($classId);

        // 2. Lấy toàn bộ danh sách bài tập của lớp để phân loại và gom ID nhóm bài
        $assignments = $courseClass->assignments()->get();
        $allAssignmentIds = $assignments->pluck('id')->toArray();

        // Lọc ID các bài Giữa Khóa
        $midtermIds = $assignments->filter(function($item) {
            $text = mb_strtolower($item->type . ' ' . $item->title);
            return str_contains($text, 'giua') || str_contains($text, 'giữa');
        })->pluck('id')->toArray();

        // Lọc ID các bài Cuối Khóa
        $finalIds = $assignments->filter(function($item) {
            $text = mb_strtolower($item->type . ' ' . $item->title);
            return str_contains($text, 'cuoi') || str_contains($text, 'cuối');
        })->pluck('id')->toArray();

        // 3. Phân trang danh sách học viên hiển thị trên table (15 học viên / trang)
        $studentsPaginator = $courseClass->students()->paginate(15);

        // Lấy tất cả học viên không phân trang để tính toán cục thống kê trên Top Card
        $allStudents = $courseClass->students()->get();

        // Đóng gói hàm Closure tái sử dụng tính toán chỉ số cho từng học viên
        $calculateStudentMetrics = function($student) use ($allAssignmentIds, $midtermIds, $finalIds, $courseClass) {
            // Lấy điểm giữa khóa cao nhất
            $student->midterm_grade = Submission::where('user_id', $student->id)
                ->whereIn('assignment_id', $midtermIds)
                ->max('grade');

            // Lấy điểm cuối khóa cao nhất
            $student->final_grade = Submission::where('user_id', $student->id)
                ->whereIn('assignment_id', $finalIds)
                ->max('grade');

            // Đếm tổng số buổi nghỉ thông qua các Buổi học của lớp này
            $student->total_absences = Attendance::where('user_id', $student->id)
                ->whereHas('lessonSession', function($q) use ($courseClass) {
                    $q->where('course_class_id', $courseClass->id);
                })
                ->whereIn('status', ['absent', 'vắng', 'Vắng'])
                ->count();

            // Đếm số bài thiếu (Hệ thống tự nộp chấm 0 điểm)
            $student->total_missing = Submission::where('user_id', $student->id)
                ->whereIn('assignment_id', $allAssignmentIds)
                ->where('grade', 0)
                ->count();

            // XỬ LÝ LOGIC ĐẦU RA (Giữ nguyên logic gốc của bạn)
            $finalScore = $student->final_grade ?? 0.0;
            $hasExceededLimits = ($student->total_absences >= 5 || $student->total_missing >= 9);

            if ($hasExceededLimits && $finalScore < 5.0) {
                $student->output_status = 'Không đạt'; 
            } elseif ($hasExceededLimits && $finalScore >= 5.0) {
                $student->output_status = 'Đạt'; 
            } else {
                $student->output_status = ($finalScore >= 5.0) ? 'Đạt' : 'Không đạt';
            }

            return $student;
        };

        // Áp dụng tính toán cho danh sách phân trang hiển thị
        $studentsPaginator->getCollection()->transform($calculateStudentMetrics);

        // Áp dụng tính toán toàn bộ lớp để lấy dữ liệu làm Top Card
        $allProcessedStudents = $allStudents->map($calculateStudentMetrics);
        
        $stats = [
            'total_students' => $allProcessedStudents->count(),
            'completed_count' => $allProcessedStudents->where('output_status', 'Đạt')->count(),
            'avg_final_grade' => round($allProcessedStudents->where('final_grade', '!==', null)->avg('final_grade'), 1),
            'pass_rate'       => $allProcessedStudents->count() > 0 
                ? round(($allProcessedStudents->where('output_status', 'Đạt')->count() / $allProcessedStudents->count()) * 100, 1) 
                : 0
        ];

        // Trả về view riêng nằm trong thư mục của TA
        return view('ta.classroom.summary', compact('courseClass', 'studentsPaginator', 'stats'));
    }
}