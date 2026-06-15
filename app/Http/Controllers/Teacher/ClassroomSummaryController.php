<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\CourseClass;
use App\Models\Attendance;
use App\Models\AssignmentDistribution;
use App\Models\LearningResult;
use App\Models\LessonSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClassroomSummaryController extends Controller
{
    /**
     * Hiển thị bảng tổng kết lớp học dành cho Giảng viên
     * Logic giống hệt TA nhưng không có approval
     */
    public function index($classId)
    {
        // 1. Lấy thông tin lớp học
        $class = CourseClass::where('id', $classId)
            ->with(['course', 'students'])
            ->firstOrFail();

        $students = $class->students;
        $lessonSessionIds = LessonSession::where('course_class_id', $classId)->pluck('id');
        $totalStudents = $students->count();

        // 2. Tính toán kết quả cho từng học viên
        foreach ($students as $student) {
            // Tính số buổi nghỉ
            $student->total_absent = Attendance::where('user_id', $student->id)
                ->whereIn('lesson_session_id', $lessonSessionIds)
                ->whereIn('status', ['absent', 'Vắng'])
                ->count();

            // Tính số bài tập thiếu
            $student->total_missing = AssignmentDistribution::whereIn('lesson_session_id', $lessonSessionIds)
                ->where('close_time', '<', now())
                ->whereNotExists(function ($query) use ($student) {
                    $query->select(DB::raw(1))
                        ->from('submissions')
                        ->whereColumn('submissions.assignment_distribution_id', 'assignment_distributions.id')
                        ->where('submissions.user_id', $student->id);
                })
                ->count();

            // Lấy điểm từ learning_results
            $learningResult = LearningResult::where('user_id', $student->id)
                ->where('course_class_id', $classId)
                ->first();

            $student->midterm_grade = $learningResult ? $learningResult->midterm_grade : null;
            $student->final_grade = $learningResult ? $learningResult->final_grade : null;
            $student->approval_status = $learningResult ? $learningResult->approval_status : 'Chờ';

            // Tính output_status
            $outputOverall = (float)($class->course->output_overall ?? 0);
            $finalGrade = $student->final_grade !== null ? (float)$student->final_grade : null;

            $isConditionBreached = ($student->total_absent >= 5 || $student->total_missing >= 9);
            $isGradeAchieved = ($finalGrade !== null && $finalGrade >= $outputOverall);

            if ($isConditionBreached && !$isGradeAchieved) {
                $student->output_status = 'Không đạt';
            } else {
                $student->output_status = 'Đạt';
            }
        }

        // 3. Tính toán dữ liệu thống kê cho Dashboard
        $passedCount = $students->where('output_status', 'Đạt')->count();
        $failedCount = $students->where('output_status', 'Không đạt')->count();
        
        $passRate = $totalStudents > 0 ? round(($passedCount / $totalStudents) * 100, 1) : 0;
        $failRate = $totalStudents > 0 ? round(($failedCount / $totalStudents) * 100, 1) : 0;
        
        $validFinalGrades = $students->whereNotNull('final_grade');
        $avgFinalGrade = $validFinalGrades->count() > 0 ? round($validFinalGrades->avg('final_grade'), 2) : 0;
        $totalStudentsMissing = $students->where('total_missing', '>', 0)->count();

        // 4. Dữ liệu biểu đồ
        $chartData = [
            'labels'   => $students->pluck('name')->toArray(),
            'absents'  => $students->pluck('total_absent')->toArray(),
            'missings' => $students->pluck('total_missing')->toArray(),
            'passed'   => $passedCount,
            'failed'   => $failedCount,
        ];

        return view('teacher.classes.summary', compact(
            'class', 'students', 
            'totalStudents', 'passedCount', 'failedCount', 
            'passRate', 'failRate', 'avgFinalGrade', 'totalStudentsMissing', 'chartData'
        ));
    }
}