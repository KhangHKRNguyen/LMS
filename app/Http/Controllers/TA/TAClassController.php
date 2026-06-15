<?php

namespace App\Http\Controllers\TA;

use App\Http\Controllers\Controller;
use App\Models\CourseClass;
use App\Models\Attendance;
use App\Models\AssignmentDistribution;
use App\Models\LearningResult;
use App\Models\LessonSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TAClassController extends Controller
{
    /**
     * Hiển thị bảng điều khiển danh sách các lớp học được phân công cho TA này
     */
    public function index(Request $request)
    {
        $ta = Auth::user();
        $search = trim($request->input('search', ''));

        // Chỉ lấy những lớp học mà TA này tham gia quản lý (Bảng trung gian class_user)
        $classes = CourseClass::whereHas('users', fn($q) => $q->where('user_id', $ta->id))
            ->with(['course'])
            ->withCount('students') // Đếm số học viên thực tế (role_id = 4)
            ->when($search, function ($query) use ($search) {
                return $query->where('class_name', 'LIKE', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('ta.dashboard', compact('classes'));
    }
    public function summary($classId)
    {
        $ta = Auth::user();

        // 1. Lấy thông tin lớp học thuộc quyền quản lý của TA
        $class = CourseClass::where('id', $classId)
            ->with(['course', 'students'])
            ->firstOrFail();

        $students = $class->students;
        $lessonSessionIds = LessonSession::where('course_class_id', $classId)->pluck('id');
        $totalStudents = $students->count();

        // 2. Tính toán kết quả cho từng học viên
        foreach ($students as $student) {
            $student->total_absent = Attendance::where('user_id', $student->id)
                ->whereIn('lesson_session_id', $lessonSessionIds)
                ->whereIn('status', ['absent', 'Vắng'])
                ->count();

            $student->total_missing = AssignmentDistribution::whereIn('lesson_session_id', $lessonSessionIds)
                ->where('close_time', '<', now())
                ->whereNotExists(function ($query) use ($student) {
                    $query->select(DB::raw(1))
                        ->from('submissions')
                        ->whereColumn('submissions.assignment_distribution_id', 'assignment_distributions.id')
                        ->where('submissions.user_id', $student->id);
                })
                ->count();

            $learningResult = LearningResult::where('user_id', $student->id)
                ->where('course_class_id', $classId)
                ->first();

            $student->midterm_grade = $learningResult ? $learningResult->midterm_grade : null;
            $student->final_grade = $learningResult ? $learningResult->final_grade : null;
            $student->approval_status = $learningResult ? $learningResult->approval_status : 'Chờ';

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

        $isAllApproved = $students->isNotEmpty() && $students->every('approval_status', 'Đã duyệt');

        // =========================================================================
        // 4. KIỂM TRA ĐIỀU KIỆN PHÊ DUYỆT (MỚI BỔ SUNG)
        // =========================================================================
        $disabledReasons = [];

        // Điều kiện 1: Chưa hoàn tất điểm danh tất cả các buổi học
        $totalSessionsCount = $lessonSessionIds->count();
        $completedAttendanceCount = LessonSession::where('course_class_id', $classId)
            ->where('attendance_status', 'đã điểm danh')
            ->count();

        if ($completedAttendanceCount < $totalSessionsCount || $totalSessionsCount == 0) {
            $disabledReasons[] = "Chưa hoàn tất điểm danh đầy đủ các buổi học (Mới điểm danh: $completedAttendanceCount/$totalSessionsCount buổi).";
        }

        // Điều kiện 2: Còn bài tập đã nộp nhưng chưa chấm điểm (score/grade bị null)
        $ungradedSubmissionsCount = DB::table('submissions')
            ->whereIn('assignment_distribution_id', AssignmentDistribution::whereIn('lesson_session_id', $lessonSessionIds)->pluck('id'))
            ->whereNull('total_grade') 
            ->count();

        if ($ungradedSubmissionsCount > 0) {
            $disabledReasons[] = "Còn $ungradedSubmissionsCount bài tập do học viên nộp lên chưa được chấm điểm số.";
        }

        // Điều kiện 3: Chưa nhập đầy đủ điểm giữa kỳ và cuối kỳ cho toàn bộ học viên
        $studentsWithCompleteGrades = LearningResult::where('course_class_id', $classId)
            ->whereIn('user_id', $students->pluck('id'))
            ->whereNotNull('midterm_grade')
            ->whereNotNull('final_grade')
            ->count();

        if ($studentsWithCompleteGrades < $totalStudents || $totalStudents == 0) {
            $disabledReasons[] = "Chưa cập nhật đủ cả điểm Giữa khóa & Cuối khóa cho toàn bộ học viên (Mới hoàn thành: $studentsWithCompleteGrades/$totalStudents học viên).";
        }

        $isApproveDisabled = !empty($disabledReasons);
        // =========================================================================

        $chartData = [
            'labels'   => $students->pluck('name')->toArray(),
            'absents'  => $students->pluck('total_absent')->toArray(),
            'missings' => $students->pluck('total_missing')->toArray(),
            'passed'   => $passedCount,
            'failed'   => $failedCount,
        ];

        return view('ta.classes.summary', compact(
            'class', 'students', 'isAllApproved', 
            'totalStudents', 'passedCount', 'failedCount', 
            'passRate', 'failRate', 'avgFinalGrade', 'totalStudentsMissing', 'chartData',
            'isApproveDisabled', 'disabledReasons' // Truyền biến mới qua View
        ));
    }

    /**
     * Phê duyệt kết quả tổng kết hàng loạt cho toàn bộ học viên trong lớp
     */
    public function approveSummary(Request $request, $classId)
    {
        $class = CourseClass::where('id', $classId)
            ->whereHas('users', fn($q) => $q->where('user_id', Auth::id()))
            ->firstOrFail();

        $studentIds = $class->students()->pluck('users.id');

        foreach ($studentIds as $studentId) {
            LearningResult::updateOrCreate(
                [
                    'user_id' => $studentId,
                    'course_class_id' => $classId,
                ],
                [
                    'approval_status' => 'Đã duyệt',
                    'approved_date' => now()->toDateString(),
                ]
            );
        }

        return redirect()->back()->with('success', 'Phê duyệt toàn bộ kết quả tổng kết thành công!');
    }
}