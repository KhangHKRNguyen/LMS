<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CourseClass;
use App\Models\AssignmentDistribution;
use App\Models\Submission;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Services\IeltsScoreService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ClassController extends Controller
{
    // Danh sách lớp học ngoài Dashboard
    public function index()
    {
        $classes = CourseClass::whereHas('users', function ($query) {
            $query->where('user_id', Auth::id());
        })
        ->with(['course'])
        ->withCount('students')
        ->get();

        return view('student.dashboard', compact('classes'));
    }

    // Không gian lớp học - Danh sách bài tập
    // Không gian lớp học - Danh sách bài tập
    public function show(CourseClass $class)
    {
        if (!$class->users()->where('user_id', Auth::id())->exists()) {
            abort(403, 'Bạn không có quyền truy cập lớp học này.');
        }

        $distributions = AssignmentDistribution::whereHas('lessonSession', function ($query) use ($class) {
            $query->where('course_class_id', $class->id);
        })
        ->with(['assignment.assignmentType', 'lessonSession', 'submissions' => function($query) {
            $query->where('user_id', Auth::id());
        }])
        ->get();
        
        // ĐOẠN MỚI ĐÃ SỬA (Khớp với cấu trúc thư mục chứa file index.blade.php của bạn):
        return view('student.classes.assignments.index', compact('class', 'distributions'));
    }

    // Chi tiết bài tập & Lịch sử lượt làm bài
    public function assignmentDetail($classId, $distributionId)
    {
        $distribution = AssignmentDistribution::with('assignment')->findOrFail($distributionId);
        $class = CourseClass::findOrFail($classId);

        $submissions = Submission::where('user_id', Auth::id())
            ->where('assignment_distribution_id', $distributionId)
            ->orderBy('attempt_number', 'asc')
            ->get();

        $attemptedCount = $submissions->count();
        $now = now();
        $isOpen = true;
        
        if ($distribution->open_time && $now->lt($distribution->open_time)) $isOpen = false;
        if ($distribution->close_time && $now->gt($distribution->close_time)) $isOpen = false;
        if ($distribution->max_attempts && $attemptedCount >= $distribution->max_attempts) $isOpen = false;

        $buttonState = [
            'status' => $isOpen ? 'enabled' : 'disabled',
            'class' => $isOpen ? ($attemptedCount > 0 ? 'btn-outline-primary' : 'btn-primary') : 'btn-secondary',
            'text' => $isOpen ? ($attemptedCount > 0 ? 'LÀM LẠI BÀI' : 'BẮT ĐẦU LÀM BÀI') : 'BÀI TẬP ĐANG KHÓA',
            'link' => route('student.classes.assignments.take', [$classId, $distributionId])
        ];

        return view('student.classes.assignments.detail', compact('class', 'distribution', 'submissions', 'buttonState'));
    }

    // Giao diện làm bài thi
    public function takeAssignment(CourseClass $class, AssignmentDistribution $distribution)
    {
        if (!$class->users()->where('user_id', Auth::id())->exists()) {
            abort(403, 'Bạn không có quyền truy cập lớp học này.');
        }

        $distribution->load(['assignment.questions.options', 'assignment.questions.keywords', 'lessonSession']);

        return view('student.classes.assignments.take', compact('class', 'distribution'));
    }

    // Logic Nộp bài & Chấm điểm tự động các phần trắc nghiệm/điền từ
    public function submitAssignment(Request $request, $classId, $distributionId)
    {
        $distribution = AssignmentDistribution::findOrFail($distributionId);
        
        DB::beginTransaction();
        try {
            $attemptNumber = Submission::where('user_id', Auth::id())
                ->where('assignment_distribution_id', $distributionId)
                ->count() + 1;

            // 1. Tạo bản ghi Submission mới
            $submission = Submission::create([
                'submission_time' => now(),
                'attempt_number' => $attemptNumber,
                'status' => 'submitted',
                'user_id' => Auth::id(),
                'assignment_distribution_id' => $distributionId,
            ]);

            $questions = Question::where('assignment_id', $distribution->assignment_id)
                ->with(['options', 'keywords'])
                ->get();

            $correctListening = 0; $totalListening = 0;
            $correctReading = 0; $totalReading = 0;

            foreach ($questions as $question) {
                // Định dạng giả định skill_id: 1 = Listening, 2 = Reading
                $isListening = ($question->skill_id == 1);
                $isReading = ($question->skill_id == 2);

                // A. Xử lý câu hỏi trắc nghiệm
                if ($question->question_type === 'trac_nghiem') {
                    if ($isListening) $totalListening++;
                    if ($isReading) $totalReading++;

                    // Ép kiểu về số nguyên để tránh lỗi so sánh chuỗi với số (String vs Integer)
                    $selectedOptionId = $request->input('answers.' . $question->id);
                    $isCorrect = false;

                    if ($selectedOptionId) {
                        $selectedOptionId = (int)$selectedOptionId; // <--- THÊM DÒNG NÀY

                        $option = $question->options->firstWhere('id', $selectedOptionId);
                        if ($option && $option->is_correct) {
                            $isCorrect = true;
                            if ($isListening) $correctListening++;
                            if ($isReading) $correctReading++;
                        }

                        DB::table('answers_multiple_choice')->insert([
                            'is_auto_correct' => $isCorrect,
                            'submission_id' => $submission->id,
                            'question_id' => $question->id,
                            'question_option_id' => $selectedOptionId,
                            'created_at' => now(), 'updated_at' => now()
                        ]);
                    }
                }
                // B. Xử lý câu hỏi điền từ vào ô trống
                elseif ($question->question_type === 'dien_tu') {
                    if ($isListening) $totalListening++;
                    if ($isReading) $totalReading++;

                    // Lấy mảng input từ học viên (nếu không có thì mặc định là mảng rỗng)
                    $inputs = $request->input('answers_blank.' . $question->id, []);

                    // XÓA BỎ IF (!EMPTY): Luôn tạo bảng ghi để lưu vết bài làm của học viên
                    $fillBlankId = DB::table('answers_fill_blank')->insertGetId([
                        'submission_id' => $submission->id,
                        'question_id' => $question->id,
                        'created_at' => now(), 'updated_at' => now()
                    ]);

                    $allBlanksCorrect = true;
                    
                    // Duyệt qua danh sách đáp án đúng được cấu hình trong DB để đối chiếu
                    foreach ($question->keywords as $keyword) {
                        // Lấy từ học viên nhập dựa trên blank_order, nếu không nhập thì coi như chuỗi rỗng
                        $studentText = isset($inputs[$keyword->blank_order]) ? trim($inputs[$keyword->blank_order]) : '';
                        
                        // So sánh không phân biệt hoa thường
                        $blankCorrect = (strcasecmp($studentText, trim($keyword->correct_keyword)) === 0);
                        
                        if (!$blankCorrect) {
                            $allBlanksCorrect = false;
                        }

                        // Lưu chi tiết từng ô trống vào DB
                        DB::table('answer_fill_blank_details')->insert([
                            'blank_order' => $keyword->blank_order,
                            'student_input' => $studentText,
                            'is_correct' => $blankCorrect,
                            'answer_fill_blank_id' => $fillBlankId,
                            'created_at' => now(), 'updated_at' => now()
                        ]);
                    }

                    // Nếu đúng hết tất cả các ô trong câu hỏi này thì mới được tính điểm câu đó
                    if ($allBlanksCorrect && $question->keywords->count() > 0) {
                        if ($isListening) $correctListening++;
                        if ($isReading) $correctReading++;
                    }
                }
                // C. Xử lý kỹ năng tự luận (Writing)
                elseif ($question->question_type === 'writing') {
                    $essayContent = $request->input('answers_writing.' . $question->id, '');
                    $wordCount = $essayContent ? count(explode(' ', preg_replace('/\s+/', ' ', trim($essayContent)))) : 0;

                    DB::table('answers_writing')->insert([
                        'essay_content' => $essayContent,
                        'word_count' => $wordCount,
                        'submission_id' => $submission->id,
                        'question_id' => $question->id,
                        'created_at' => now(), 'updated_at' => now()
                    ]);
                }
                // D. Xử lý kỹ năng nói (Speaking) - Nhận từ file tải lên lẫn ghi âm trực tiếp
                elseif ($question->question_type === 'speaking') {
                    $audioPath = null;
                    if ($request->hasFile('answers_speaking.' . $question->id)) {
                        $file = $request->file('answers_speaking.' . $question->id);
                        $audioPath = $file->store('submissions/speaking', 'public');
                    }

                    DB::table('answers_speaking')->insert([
                        'audio_file_path' => $audioPath ?? '',
                        'duration_seconds' => null,
                        'submission_id' => $submission->id,
                        'question_id' => $question->id,
                        'created_at' => now(), 'updated_at' => now()
                    ]);
                }
            }

            // Tự động quy đổi điểm IELTS Band Score bằng Service nếu có sẵn
            if (class_exists('App\Services\IeltsScoreService')) {
                $listeningBand = IeltsScoreService::calculateSkillBand($correctListening, $totalListening);
                $readingBand = IeltsScoreService::calculateSkillBand($correctReading, $totalReading);
            } else {
                $listeningBand = $totalListening > 0 ? round(($correctListening / $totalListening) * 9, 1) : null;
                $readingBand = $totalReading > 0 ? round(($correctReading / $totalReading) * 9, 1) : null;
            }

            $submission->update([
                'listening_grade' => $listeningBand,
                'reading_grade' => $readingBand,
            ]);

            DB::commit();
            return redirect()->route('student.classes.assignments.detail', [$classId, $distributionId])
                             ->with('success', 'Nộp bài thành công! Hãy đợi giáo viên chấm điểm Writing/Speaking.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Lỗi trong quá trình nộp bài: ' . $e->getMessage());
        }
    }

    // Xem chi tiết bài thi sau khi nộp
    public function viewSubmission(CourseClass $class, AssignmentDistribution $distribution, $submissionId)
    {
        if (!$class->users()->where('user_id', Auth::id())->exists()) {
            abort(403, 'Bạn không có quyền truy cập.');
        }

        $submission = $distribution->submissions()
            ->where('user_id', Auth::id())
            ->where('id', $submissionId)
            ->firstOrFail();

        $questions = Question::where('assignment_id', $distribution->assignment_id)->with(['options', 'keywords'])->get();

        // Nạp câu trả lời của học viên từ DB lên để so sánh kết quả ở View
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

        return view('student.classes.assignments.submission_detail', compact(
            'class', 'distribution', 'submission', 'questions', 'mcAnswers', 'fbAnswers', 'fbDetails', 'writingAnswers', 'speakingAnswers'
        ));
    }

    public function materials(CourseClass $class) { return "Tính năng tài liệu đang cập nhật."; }
    public function summary($id) { return "Tính năng kết quả học tập đang cập nhật."; }
}