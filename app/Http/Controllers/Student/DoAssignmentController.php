<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Submission;
use App\Models\StudentAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DoAssignmentController extends Controller
    {
        /**
         * Danh sách bài tập
         */
        public function index(Request $request)
        {
            // Lấy danh sách lớp của học viên
            $classIds = auth()->user()->courseClasses()->pluck('course_classes.id');

            // Nếu không có quan hệ, lấy từ class_user table
            if ($classIds->isEmpty()) {
                $classIds = \DB::table('class_user')
                    ->where('user_id', auth()->id())
                    ->pluck('course_class_id');
            }
    $studentClasses = \App\Models\CourseClass::whereIn('id', $classIds)->get();
            // Lấy danh sách assignments của các lớp
            $query = Assignment::whereIn('course_class_id', $classIds)
                ->where('is_visible', true)
                ->with(['courseClass', 'submissions' => function($q) {
                    $q->where('user_id', auth()->id());
                }]);
                if ($request->has('class_id') && $request->class_id != '') {
                $query->where('course_class_id', $request->class_id);
            }

            // 5. Sắp xếp và lấy ra danh sách bài tập sau khi lọc
            $assignments = $query->orderBy('due_time', 'asc')->get();

            return view('student.assignments.index', compact('assignments','studentClasses'));
        }

        /**
         * Hiển thị form làm bài
         */
        public function show($assignmentId)
        {
            $assignment = Assignment::with(['questions', 'courseClass'])->findOrFail($assignmentId);

            if (!$assignment->is_visible) {
                abort(404, 'Bài tập này đã bị giáo viên ẩn hoặc không tồn tại.');
            }

            // Kiểm tra học viên có quyền làm bài này không
            $userClassIds = auth()->user()->courseClasses()->pluck('course_classes.id');
            
            if ($userClassIds->isEmpty()) {
                $userClassIds = \DB::table('class_user')
                    ->where('user_id', auth()->id())
                    ->pluck('course_class_id');
            }

            if (!$userClassIds->contains($assignment->course_class_id)) {
                abort(403, 'Không có quyền truy cập bài tập này');
            }

            // Kiểm tra bài tập đã mở chưa
            if (now() < $assignment->open_time) {
                abort(403, 'Bài tập này chưa được mở');
            }

            // Lấy submission cũ nếu có
            $submission = Submission::where('assignment_id', $assignmentId)
                ->where('user_id', auth()->id())
                ->with('studentAnswers')
                ->first();

            $isOverdue = now() > $assignment->due_time;

            return view('student.assignments.do', compact('assignment', 'submission', 'isOverdue'));
        }

    public function viewFile(Submission $submission)
    {
        if (!$submission->file_path) {
            abort(404);
        }

        return Storage::disk('local')
            ->response($submission->file_path);
    }

        /**
         * Lưu bài nộp
         */
        public function store(Request $request, $assignmentId)
        {
            $assignment = Assignment::findOrFail($assignmentId);
            if (!$assignment->is_visible) {
                abort(404, 'Bài tập này đã bị ẩn, bạn không thể nộp bài.');
            }
            $isOverdue = now() > $assignment->due_time;

            // Validate theo loại bài tập
            if ($assignment->type === 'Trắc nghiệm') {
                // FIX: Khi hết giờ (auto-submit) → Chấp nhận partial/no answers
                // Khi chưa hết giờ → Yêu cầu đầy đủ
                $answerRules = $isOverdue 
                    ? 'nullable|array'  // Chấp nhận 0 answers hoặc partial
                    : 'required|array'; // Bắt buộc khi chưa hết giờ
                
                $validated = $request->validate([
                    'answers' => $answerRules,
                    'answers.*.question_id' => 'sometimes|required|exists:questions,id',
                    'answers.*.selected_option' => 'sometimes|required|in:A,B,C,D'
                ], [
                    'answers.required' => 'Vui lòng trả lời tất cả các câu hỏi',
                    'answers.*.selected_option.required' => 'Vui lòng chọn đáp án',
                ]);
            } else {
        
            if ($request->hasFile('file')) {
    
    }

                // Tự luận
                $validated = $request->validate([
                    'submission_content' => 'nullable|string',
                    'file' => [
        'nullable',
        'file',
        'max:51200'
        ] ], [
                    'file.max' => 'File không được vượt quá 50MB',
                    'file.mimes' => 'File phải là PDF, DOCX, ZIP'
                ]);

    if ($request->hasFile('file')) {

        $allowed = [
            'pdf','docx','zip'
        ];

        $ext = strtolower(
            $request->file('file')->getClientOriginalExtension()
        );

        if (!in_array($ext, $allowed)) {
            return back()->withErrors([
                'file' => 'Định dạng file không được hỗ trợ.'
            ]);
        }
    }
                // Kiểm tra ít nhất phải có 1 trong 2: nội dung hoặc file
                if (empty($validated['submission_content']) && !$request->hasFile('file')) {
                    return back()->withErrors(['submission' => 'Vui lòng nhập nội dung hoặc upload file']);
                }
            }

            // Lấy hoặc tạo submission
            $submission = Submission::firstOrCreate(
                [
                    'assignment_id' => $assignmentId,
                    'user_id' => auth()->id()
                ],
                [
                    'status' => 'Đã nộp'
                ]
            );

            // Cập nhật nếu là tự luận
            if ($assignment->type === 'Tự luận') {
                $submission->submission_content = $validated['submission_content'] ?? null;

                // Xử lý upload file
                if ($request->hasFile('file')) {
                    // Xóa file cũ nếu có
                    if ($submission->file_path && Storage::exists($submission->file_path)) {
                        Storage::delete($submission->file_path);
                    }

                    // Lưu file mới
                    $file = $request->file('file');
                    $filePath = $file->store('submissions/' . $assignmentId, 'local');
                    $submission->file_path = $filePath;
                }

                $submission->grade = null; // Bài tự luận để null để Thành viên 3 chấm tay sau
                $submission->status = 'Đã nộp';
                $submission->save();
            } else {
                // TRẮC NGHIỆM: Lưu student answers
                // Xóa câu trả lời cũ nếu có
                $submission->studentAnswers()->delete();
                
                $totalQuestions = $assignment->questions->count();
                $correctAnswersCount = 0;
                
                $answers = $validated['answers'] ?? [];
                
                // Tạo câu trả lời mới
                if (is_array($answers) && count($answers) > 0) {
                    foreach ($answers as $answer) {
                        // ✅ FIX: Kiểm tra xem key tồn tại trước khi access
                        if (!isset($answer['question_id']) || !isset($answer['selected_option'])) {
                            continue; // Bỏ qua câu hỏi thiếu dữ liệu
                        }
                        
                        $question = $assignment->questions->firstWhere('id', $answer['question_id']);
                        $isCorrect = false;
                        
                        if ($question) {
                            // So sánh trực tiếp: đáp án học viên chọn === đáp án đúng của giáo viên
                            $isCorrect = ($answer['selected_option'] === $question->correct_option);
                            if ($isCorrect) {
                                $correctAnswersCount++;
                            }
                        }
                        
                        StudentAnswer::create([
                            'submission_id' => $submission->id,
                            'question_id' => $answer['question_id'],
                            'selected_option' => $answer['selected_option']
                        ]);
                    }
                }
                // Nếu không có answers (answers = []), correctAnswersCount vẫn = 0

                // Tính điểm theo thang 10 và làm tròn 2 chữ số thập phân
                // Nếu 0 câu trả lời → Score = 0/totalQuestions * 10 = 0.00
                $score = $totalQuestions > 0 ? ($correctAnswersCount / $totalQuestions) * 10 : 0;
                $submission->grade = round($score, 2); 

                $submission->status = 'Đã chấm (Tự động)';
                $submission->save();
            }

        // Sau khi lưu dữ liệu vào bảng Submissions và StudentAnswers xong...

    if ($assignment->type === 'Trắc nghiệm') {
        // Ở lại trang để xem ngay kết quả xanh đỏ
        return redirect()->route('student.assignments.show', $assignment->id)
                        ->with('success', 'Nộp bài trắc nghiệm thành công!');
    } else {
        // Ra ngoài danh sách đối với bài tự luận
        return redirect()->route('student.assignments.index')
                        ->with('success', 'Nộp bài tự luận thành công! Bài làm của bạn đang chờ giáo viên chấm.');
    }
        }}