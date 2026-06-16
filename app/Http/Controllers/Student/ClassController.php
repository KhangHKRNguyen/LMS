<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CourseClass;
use App\Models\AssignmentDistribution;
use App\Models\Submission;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Material;
use App\Services\IeltsScoreService;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ClassController extends Controller
{
    // Danh sách lớp học ngoài Dashboard
    public function index(Request $request)
    {
        $search = trim($request->input('search', ''));

        $classes = CourseClass::whereHas('users', function ($query) {
            $query->where('user_id', Auth::id());
        })
        ->with(['course'])
        ->withCount('students')
        ->when($search, function ($query) use ($search) {
            return $query->where('class_name', 'LIKE', "%{$search}%");
        })
        ->get();

        return view('student.dashboard', compact('classes'));
    }

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

            $scoreUpdates = [
                'listening_grade' => $listeningBand,
                'reading_grade' => $readingBand,
            ];

            $submission->fill($scoreUpdates);

            $hasManualQuestions = $questions->contains(function ($question) {
                return in_array($question->question_type, ['writing', 'speaking'], true);
            });

            if (! $hasManualQuestions) {
                $submission->total_grade = IeltsScoreService::calculateOverall([
                    $submission->listening_grade,
                    $submission->reading_grade,
                    $submission->writing_grade,
                    $submission->speaking_grade,
                ]);
                $submission->status = 'graded';
            }

            $submission->save();

            DB::commit();
            $successMessage = $hasManualQuestions
                ? 'Nop bai thanh cong! Hay doi giao vien cham diem Writing/Speaking.'
                : 'Nop bai thanh cong! Bai lam da duoc cham tu dong va tinh overall.';

            return redirect()->route('student.classes.assignments.detail', [$classId, $distributionId])
                             ->with('success', $successMessage);
            /*
            return redirect()->route('student.classes.assignments.detail', [$classId, $distributionId])
                             ->with('success', 'Nộp bài thành công! Hãy đợi giáo viên chấm điểm Writing/Speaking.');

            */
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

    public function feedbackChat(CourseClass $class, AssignmentDistribution $distribution, $submissionId)
    {
        if (!$class->users()->where('user_id', Auth::id())->exists()) {
            abort(403, 'Bạn không có quyền truy cập.');
        }

        $submission = $distribution->submissions()
            ->where('user_id', Auth::id())
            ->where('id', $submissionId)
            ->firstOrFail();

        // Lấy lịch sử đoạn chat của bài làm này
        $chats = \App\Models\Feedback::where('submission_id', $submission->id)
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->get();

        return view('student.classes.assignments.feedback', compact('class', 'distribution', 'submission', 'chats'));
    }

    // Xử lý gửi tin nhắn từ Học viên
    public function sendFeedback(Request $request, $classId, $distributionId, $submissionId)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        // Đảm bảo đúng bài làm của chính học viên này
        $submission = Submission::where('user_id', Auth::id())
            ->where('id', $submissionId)
            ->firstOrFail();

        \App\Models\Feedback::create([
            'content' => $request->input('content'),
            'user_id' => Auth::id(),
            'submission_id' => $submission->id,
        ]);

        $distribution = AssignmentDistribution::with('assignment', 'teacher')->find($distributionId);
        if ($distribution?->teacher) {
            app(NotificationService::class)->send(
                'Học viên gửi thắc mắc về bài tập',
                Auth::user()->name . " đã gửi thắc mắc trong bài {$distribution->assignment?->title}.",
                $distribution->teacher,
                Auth::id(),
                'feedback_student_question'
            );
        }

        return redirect()->back()->with('success', 'Gửi phản hồi đến giảng viên thành công!');
    }

    public function materials(Request $request, $classId)
    {
        $student = Auth::user();

        // Xác thực học viên có thuộc lớp học này không để bảo mật dữ liệu
        $class = CourseClass::where('id', $classId)
            ->whereHas('users', fn($q) => $q->where('user_id', $student->id))
            ->firstOrFail();

        $search     = $request->get('search', '');
        $filterType = $request->get('type', '');

        // 1. Thống kê số lượng file theo từng định dạng từ toàn bộ tài liệu của lớp
        $allMaterials = $class->materials()->get();
        $stats = [
            'total' => $allMaterials->count(),
            'pdf'   => $allMaterials->filter(fn($m) => strtolower(pathinfo($m->file_path, PATHINFO_EXTENSION)) === 'pdf')->count(),
            'word'  => $allMaterials->filter(fn($m) => in_array(strtolower(pathinfo($m->file_path, PATHINFO_EXTENSION)), ['doc', 'docx']))->count(),
            'excel' => $allMaterials->filter(fn($m) => in_array(strtolower(pathinfo($m->file_path, PATHINFO_EXTENSION)), ['xls', 'xlsx']))->count(),
            'ppt'   => $allMaterials->filter(fn($m) => in_array(strtolower(pathinfo($m->file_path, PATHINFO_EXTENSION)), ['ppt', 'pptx']))->count(),
        ];

        // 2. Tạo query lấy danh sách tài liệu chính có áp dụng tìm kiếm & bộ lọc dropdown
        $query = $class->materials();

        if (!empty($search)) {
            $query->where('title', 'like', '%' . $search . '%');
        }

        if (!empty($filterType)) {
            switch ($filterType) {
                case 'pdf':
                    $query->where('file_path', 'like', '%.pdf');
                    break;
                case 'word':
                    $query->where(fn($q) => $q->where('file_path', 'like', '%.doc')->orWhere('file_path', 'like', '%.docx'));
                    break;
                case 'excel':
                    $query->where(fn($q) => $q->where('file_path', 'like', '%.xls')->orWhere('file_path', 'like', '%.xlsx'));
                    break;
                case 'powerpoint':
                    $query->where(fn($q) => $q->where('file_path', 'like', '%.ppt')->orWhere('file_path', 'like', '%.pptx'));
                    break;
                case 'image':
                    $query->where(fn($q) => $q->where('file_path', 'like', '%.jpg')->orWhere('file_path', 'like', '%.jpeg')->orWhere('file_path', 'like', '%.png'));
                    break;
                case 'archive':
                    $query->where(fn($q) => $q->where('file_path', 'like', '%.zip')->orWhere('file_path', 'like', '%.rar'));
                    break;
            }
        }

        // Phân trang dữ liệu giống như bên giáo viên
        $materials = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('student.materials.index', compact('class', 'materials', 'stats', 'search', 'filterType'));
    }

    /**
     * Xử lý tải tài liệu an toàn cho Học viên
     */
    public function downloadMaterial(Material $material)
    {
        $student = Auth::user();

        // Kiểm tra xem học viên có thực sự thuộc lớp sở hữu tài liệu này không
        CourseClass::whereHas('users', fn($q) => $q->where('user_id', $student->id))
            ->findOrFail($material->course_class_id);

        if (!Storage::disk('public')->exists($material->file_path)) {
            return back()->with('error', 'Tệp tin không tồn tại hoặc đã bị xóa khỏi hệ thống.');
        }

        $fullPath = Storage::disk('public')->path($material->file_path);
        $ext      = pathinfo($material->file_path, PATHINFO_EXTENSION);
        $fileName = $material->title . '.' . $ext;

        return response()->download($fullPath, $fileName);
    }

    /**
     * Hiển thị kết quả tổng kết cá nhân của Học viên
     */
    public function summary(CourseClass $class)
    {
        $student = Auth::user();
        $classId = $class->id;

        // 2. Lấy kết quả học tập từ bảng learning_results
        $learningResult = \App\Models\LearningResult::where('user_id', $student->id)
            ->where('course_class_id', $classId)
            ->first();

        // 3. Kiểm tra logic phê duyệt: Phải tồn tại bản ghi và có trạng thái là 'Đã duyệt'
        $isApproved = $learningResult && $learningResult->approval_status === 'Đã duyệt';

        // 4. Tính toán dữ liệu chuyên cần & bài tập thực tế để hiển thị chi tiết cho học viên
        $lessonSessionIds = \App\Models\LessonSession::where('course_class_id', $classId)->pluck('id');
        
        // Tính số buổi vắng
        $totalAbsent = \App\Models\Attendance::where('user_id', $student->id)
            ->whereIn('lesson_session_id', $lessonSessionIds)
            ->whereIn('status', ['absent', 'Vắng'])
            ->count();

        // Tính số bài tập thiếu
        $distributionIds = \App\Models\AssignmentDistribution::whereIn('lesson_session_id', $lessonSessionIds)->pluck('id');
        $submittedCount = \App\Models\Submission::where('user_id', $student->id)
            ->whereIn('assignment_distribution_id', $distributionIds)
            ->count();
        $totalMissing = max(0, $distributionIds->count() - $submittedCount);

        $finalGrade = $learningResult ? $learningResult->final_grade : null;
        $outputOverall = $class->course->output_overall ?? 0;

        if ($finalGrade !== null && $finalGrade >= $outputOverall) {
            $outputStatus = 'Đạt';
        } else {
            $outputStatus = 'Không đạt';
        }
        // =========================================================================

        // 5. Trả về view tổng kết cá nhân kèm theo các biến trạng thái đầu ra
        return view('student.classes.summary', compact(
            'class',
            'student',
            'isApproved', 
            'totalAbsent', 
            'totalMissing', 
            'finalGrade', 
            'outputOverall', 
            'outputStatus',
            'learningResult'
        ));
    }
}
