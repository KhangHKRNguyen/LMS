<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CourseClass;
use App\Models\AssignmentDistribution;
use App\Models\Submission;
use App\Models\Question;
use App\Models\Feedback;
use App\Services\IeltsScoreService;
use Illuminate\Support\Facades\DB;
use App\Models\LearningResult;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth; 

class SubmissionController extends Controller
{
    // 1. Giao diện hiển thị danh sách bài nộp của một lượt giao bài
    public function index(CourseClass $class, AssignmentDistribution $distribution)
    {
        $distribution->load('assignment', 'lessonSession');

        // Lấy danh sách bài nộp thuộc đợt giao bài này kèm thông tin học viên
        $submissions = Submission::where('assignment_distribution_id', $distribution->id)
            ->with('user')
            ->orderBy('submission_time', 'desc')
            ->get();

        $totalSubmissions = $submissions->count();

        return view('teacher.submissions.index', compact('class', 'distribution', 'submissions', 'totalSubmissions'));
    }

    // 2. Giao diện chấm điểm chi tiết bài nộp (Giống form làm bài nhưng có đáp án đúng)
    public function grade(CourseClass $class, Submission $submission)
    {
        $submission->load('user', 'assignmentDistribution.assignment');
        $distribution = $submission->assignmentDistribution;

        // Lấy toàn bộ câu hỏi của bài tập, nạp sẵn options (đáp án trắc nghiệm) và keywords (điền từ)
        $questions = Question::where('assignment_id', $distribution->assignment_id)
            ->with(['options', 'keywords'])
            ->get();

        // Nạp câu trả lời đã nộp của học viên từ DB
        $mcAnswers = DB::table('answers_multiple_choice')->where('submission_id', $submission->id)->get()->keyBy('question_id');
        $fbAnswers = DB::table('answers_fill_blank')->where('submission_id', $submission->id)->get()->keyBy('question_id');
        
        $fbDetails = [];
        if ($fbAnswers->count() > 0) {
            $fbDetails = DB::table('answer_fill_blank_details')
                ->whereIn('answer_fill_blank_id', $fbAnswers->pluck('id'))
                ->get()
                ->groupBy('answer_fill_blank_id');
        }
        
        $writingAnswers = DB::table('answers_writing')->where('submission_id', $submission->id)->get()->keyBy('question_id');
        $speakingAnswers = DB::table('answers_speaking')->where('submission_id', $submission->id)->get()->keyBy('question_id');

        return view('teacher.submissions.grade', compact(
            'class', 'distribution', 'submission', 'questions', 
            'mcAnswers', 'fbAnswers', 'fbDetails', 'writingAnswers', 'speakingAnswers'
        ));
    }

    // 3. Xử lý lưu điểm chấm tự luận từ Giáo viên & Cập nhật lại Overall Band Score
    public function postGrade(Request $request, CourseClass $class, Submission $submission)
    {
        $submission->load('assignmentDistribution.assignment');
        
        // 1. Lưu điểm Writing nếu có
        if ($request->has('writing_scores')) {
            foreach ($request->input('writing_scores') as $ansId => $score) {
                DB::table('answers_writing')->where('id', $ansId)->update([
                    'teacher_score' => $score !== null ? (float)$score : null,
                    'updated_at' => now()
                ]);
            }
            // Lấy điểm trung bình cộng của các câu viết luận để làm điểm Writing Grade của lượt thi
            $avgWriting = DB::table('answers_writing')->where('submission_id', $submission->id)->avg('teacher_score');
            $submission->writing_grade = $avgWriting !== null ? round((float)$avgWriting, 2) : null;
        }

        // 2. Lưu điểm Speaking nếu có
        if ($request->has('speaking_scores')) {
            foreach ($request->input('speaking_scores') as $ansId => $score) {
                DB::table('answers_speaking')->where('id', $ansId)->update([
                    'teacher_score' => $score !== null ? (float)$score : null,
                    'updated_at' => now()
                ]);
            }
            // Lấy điểm trung bình cộng các câu nói để làm điểm Speaking Grade của lượt thi
            $avgSpeaking = DB::table('answers_speaking')->where('submission_id', $submission->id)->avg('teacher_score');
            $submission->speaking_grade = $avgSpeaking !== null ? round((float)$avgSpeaking, 2) : null;
        }

        // 3. Tính toán lại điểm Overall (Trung bình cộng của 4 kỹ năng IELTS)
        $skillsCount = 0;
        $totalSum = 0;

        foreach (['listening_grade', 'reading_grade', 'writing_grade', 'speaking_grade'] as $skill) {
            if ($submission->$skill !== null) {
                $totalSum += (float)$submission->$skill;
                $skillsCount++;
            }
        }

        if ($skillsCount > 0) {
            $rawOverall = $totalSum / $skillsCount;
            
            // Áp dụng thuật toán làm tròn IELTS (.0, .25 -> .5, .75 -> +1.0) bằng logic của Service có sẵn
            $floor = floor($rawOverall);
            $remainder = $rawOverall - $floor;
            if ($remainder < 0.25) {
                $overall = $floor;
            } elseif ($remainder >= 0.25 && $remainder < 0.75) {
                $overall = $floor + 0.5;
            } else {
                $overall = $floor + 1.0;
            }
            $submission->total_grade = $overall;
        }

        // Cập nhật trạng thái bài nộp thành "Đã chấm"
        $submission->status = 'graded';
        $submission->teacher_comment = $request->input('teacher_comment');
        $submission->save();

        $distribution = $submission->assignmentDistribution;
        $assignment = $distribution ? $distribution->assignment : null;
        $assignmentType = $assignment ? $assignment->assignmentType : null;

        if ($assignmentType) {
            // Chuyển tên loại bài tập về chữ thường và loại bỏ khoảng trắng thừa để kiểm tra linh hoạt
            $typeName = Str::lower($assignmentType->name);
            $columnToUpdate = null;

            // Kiểm tra xem tên loại bài tập có chứa các từ khóa tương ứng hay không
            if (Str::contains($typeName, 'giữa') || Str::contains($typeName, 'mid-term')) {
                $columnToUpdate = 'midterm_grade';
            } 
            elseif (Str::contains($typeName, 'cuối') || Str::contains($typeName, 'final')) {
                $columnToUpdate = 'final_grade';
            }

            // Nếu đúng là bài thi Giữa kỳ hoặc Cuối kỳ thì thực hiện lưu vết
            if ($columnToUpdate) {
                // Xác định chính xác id lớp học hiện tại
                $courseClassId = $class->id ?? ($distribution->lessonSession->course_class_id ?? null);

                if ($courseClassId) {
                    /**
                     * Sử dụng updateOrCreate: 
                     * - Nếu học viên này ở lớp này đã có dòng kết quả, hệ thống chỉ cập nhật thêm điểm vào cột tương ứng.
                     * - Nếu chưa từng có dòng kết quả nào, hệ thống sẽ tạo mới hoàn toàn.
                     */
                    LearningResult::updateOrCreate(
                        [
                            'user_id'         => $submission->user_id,          // Mã học viên
                            'course_class_id' => $courseClassId,               // Mã lớp học
                        ],
                        [
                            $columnToUpdate   => $submission->total_grade,      // Điểm Overall sau khi chấm xong
                            'approval_status' => 'Chờ',                         // Trạng thái chờ phê duyệt mặc định
                        ]
                    );
                }
            }
        }

        return redirect()->route('teacher.submissions.index', [$class->id, $submission->assignment_distribution_id])
            ->with('success', 'Đã lưu điểm chấm bài và cập nhật IELTS Band Score thành công!');
    }

    public function feedbackChat(CourseClass $class, Submission $submission)
    {
        $submission->load('user', 'assignmentDistribution.assignment');
        $distribution = $submission->assignmentDistribution;

        // Lấy lịch sử chat xếp theo thứ tự thời gian tăng dần
        $chats = Feedback::where('submission_id', $submission->id)
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->get();

        return view('teacher.submissions.feedback', compact('class', 'distribution', 'submission', 'chats'));
    }

    // Xử lý gửi tin nhắn từ Giáo viên
    public function sendFeedback(Request $request, CourseClass $class, Submission $submission)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        Feedback::create([
            'content' => $request->input('content'),
            'user_id' => Auth::id(),
            'submission_id' => $submission->id,
        ]);

        return redirect()->back()->with('success', 'Gửi phản hồi thành công!');
    }
}