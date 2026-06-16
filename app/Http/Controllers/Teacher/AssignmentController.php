<?php

namespace App\Http\Controllers\Teacher;

use App\Models\Assignment;
use App\Models\CourseClass;
use App\Models\AssignmentDistribution;
use App\Services\ExcelImportService;
use App\Services\NotificationService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;


class AssignmentController extends Controller
{
   public function index($classId)
    {
        // 1. Tìm lớp học và đếm sĩ số học viên
        $class = CourseClass::withCount('students')->findOrFail($classId);

        // 2. BỔ SUNG QUYẾT ĐỊNH: Lấy chính xác các đợt giao bài (distributions) thuộc về lớp học này
        // Lọc thông qua buổi học (lessonSession) thuộc classId hiện tại
        $distributions = AssignmentDistribution::whereHas('lessonSession', function($q) use ($classId) {
            $q->where('course_class_id', $classId);
        })->with(['assignment', 'lessonSession'])->latest()->get();

        // 3. Truyền cả biến $class và $distributions sang view
        return view('teacher.assignments.index', compact('class', 'distributions'));
    }

    public function create()
    {
        return view('teacher.assignments.create');
    }

    public function store(Request $request, ExcelImportService $excelImportService)
    {
        // Bỏ các rule liên quan đến course_class_id, open_time, due_time
        $rules = [
            'title'            => ['required', 'string', 'max:255'],
            'content'          => ['nullable', 'string'],
            'type'             => ['required', Rule::in(['Trắc nghiệm', 'Tự luận'])],
            'attachment'       => ['nullable', 'file', 'max:10240'],
            'import_file'      => ['nullable', 'file', 'max:5120'],
            'skill'            => ['required', Rule::in(['Nghe', 'Đọc', 'Viết', 'Nói'])],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:480'],
            'passage'          => ['nullable', 'string'],
            'audio_file'       => ['nullable', 'file', 'mimes:mp3,wav,m4a', 'max:20480'],
        ];

        if ($request->input('type') === 'Trắc nghiệm' && ! $request->hasFile('import_file')) {
            $rules = array_merge($rules, [
                'questions'                  => ['required', 'array', 'min:1'],
                'questions.*.question_text'  => ['required', 'string'],
                'questions.*.option_a'       => ['required', 'string'],
                'questions.*.option_b'       => ['required', 'string'],
                'questions.*.option_c'       => ['required', 'string'],
                'questions.*.option_d'       => ['required', 'string'],
                'questions.*.correct_option' => ['required', Rule::in(['A', 'B', 'C', 'D'])],
            ]);
        }

        if ($request->input('type') === 'Tự luận') {
            $rules['content'] = ['required', 'string'];
        }

        $validated = $request->validate($rules);

        $questions = [];
        if ($validated['type'] === 'Trắc nghiệm') {
            $questions = $request->hasFile('import_file')
                ? $excelImportService->importQuestions($request->file('import_file'))
                : array_values($validated['questions']);
        }

        $assignment = DB::transaction(function () use ($request, $validated, $questions) {
            $filePath = $request->file('attachment')?->store('assignments/attachments', 'public');
            
            $audioPath = null;
            if ($validated['skill'] === 'Nghe' && $request->hasFile('audio_file')) {
                $audioPath = $request->file('audio_file')->store('assignments/audio', 'public');
            }

            // Lưu trực tiếp vào Database, KHÔNG CÓ course_class_id và do_time/open_time
            $assignment = Assignment::create([
                'title'            => $validated['title'],
                'content'          => $validated['content'] ?? null,
                'type'             => $validated['type'],
                'file_path'        => $filePath,
                'course_class_id'  => null, // Xác định đây là Đề thi gốc
                'is_visible'       => false, // Không dùng cho Đề thi gốc
                'skill'            => $validated['skill'],
                'duration_minutes' => $validated['duration_minutes'],
                'passage'          => $validated['skill'] === 'Đọc' ? ($validated['passage'] ?? null) : null,
                'audio_path'       => $audioPath,
            ]);

            foreach ($questions as $question) {
                $assignment->questions()->create([
                    'question_text'  => $question['question_text'],
                    'option_a'       => $question['option_a'],
                    'option_b'       => $question['option_b'],
                    'option_c'       => $question['option_c'],
                    'option_d'       => $question['option_d'],
                    'correct_option' => mb_strtoupper($question['correct_option']),
                    'type'           => 'single_choice',
                ]);
            }

            return $assignment;
        });

        // Điều hướng thẳng về danh sách Ngân hàng đề
        return redirect()
            ->route('teacher.assignments.index')
            ->with('success', 'Đã lưu đề thi mới vào Ngân hàng đề thành công.');
    }

    public function show(Assignment $assignment)
    {
        $assignment->load('questions');
        return view('teacher.assignments.show', compact('assignment'));
    }

    public function edit(Assignment $assignment)
    {
        return view('teacher.assignments.edit', compact('assignment'));
    }

    public function update(Request $request, Assignment $assignment)
    {
        $rules = [
            'title'            => ['required', 'string', 'max:255'],
            'content'          => ['nullable', 'string'],
            'type'             => ['required', Rule::in(['Trắc nghiệm', 'Tự luận'])],
            'attachment'       => ['nullable', 'file', 'max:10240'],
            'skill'            => ['required', Rule::in(['Nghe', 'Đọc', 'Viết', 'Nói'])],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:480'],
            'passage'          => ['nullable', 'string'],
            'audio_file'       => ['nullable', 'file', 'mimes:mp3,wav,m4a', 'max:20480'],
        ];

        if ($request->input('type') === 'Tự luận') {
            $rules['content'] = ['required', 'string'];
        }

        $validated = $request->validate($rules);

        DB::transaction(function () use ($request, $validated, $assignment) {
            if ($request->hasFile('attachment')) {
                if ($assignment->file_path) {
                    Storage::disk('public')->delete($assignment->file_path);
                }
                $assignment->file_path = $request->file('attachment')->store('assignments/attachments', 'public');
            }

            if ($validated['skill'] === 'Nghe') {
                if ($request->hasFile('audio_file')) {
                    if ($assignment->audio_path) {
                        Storage::disk('public')->delete($assignment->audio_path);
                    }
                    $assignment->audio_path = $request->file('audio_file')->store('assignments/audio', 'public');
                }
            } else {
                if ($assignment->audio_path) {
                    Storage::disk('public')->delete($assignment->audio_path);
                    $assignment->audio_path = null;
                }
            }

            // Update cấu hình Đề thi gốc
            $assignment->update([
                'title'            => $validated['title'],
                'content'          => $validated['content'] ?? null,
                'type'             => $validated['type'],
                'file_path'        => $assignment->file_path,
                'skill'            => $validated['skill'],
                'duration_minutes' => $validated['duration_minutes'],
                'passage'          => $validated['skill'] === 'Đọc' ? ($validated['passage'] ?? null) : null,
                'audio_path'       => $assignment->audio_path,
            ]);
        });

        return redirect()
            ->route('teacher.assignments.index')
            ->with('success', 'Đã cập nhật cấu hình đề thi trong Ngân hàng đề.');
    }

    private function teacherClasses()
    {
        return CourseClass::query()
            ->when(Auth::user()->role !== 'admin', function ($query) {
                $query->whereHas('users', fn ($userQuery) => $userQuery->where('users.id', Auth::id()));
            });
    }

    private function authorizeAssignment(Assignment $assignment): void
    {
        $this->authorizeClass($assignment->courseClass);
    }

    private function authorizeClass(CourseClass $courseClass): void
    {
        if (Auth::user()->role === 'admin') {
            return;
        }

        abort_unless(
            $courseClass->users()->where('users.id', Auth::id())->exists(),
            403
        );
    }

    public function toggleVisibility(Assignment $assignment)
    {
        $this->authorizeAssignment($assignment);
        $assignment->update(['is_visible' => !$assignment->is_visible]);
        $statusLabel = $assignment->is_visible ? 'hiển thị' : 'ẩn';

        return redirect()->back()->with('success', "Đã chuyển bài tập sang trạng thái: " . Str::ucfirst($statusLabel));
    }

    public function assignView($examId)
    {
        $exam = Assignment::findOrFail($examId);
        
        // SỬA TẠI ĐÂY: Thêm điều kiện lọc để chỉ lấy lớp học mà giảng viên đang đăng nhập phụ trách
        $classes = CourseClass::whereHas('users', function($q) {
                $q->where('users.id', Auth::id());
            })
            ->with(['lessonSessions' => function($q) {
                $q->orderBy('lesson_date', 'asc');
            }])
            ->get();

        // Lấy danh sách các lớp đã nhận đề thi này trước đó (Lọc thêm theo user_id để giảng viên chỉ thấy lịch sử giao của mình)
        $distributions = AssignmentDistribution::with(['lessonSession.courseClass'])
            ->where('assignment_id', $examId)
            ->where('user_id', Auth::id()) // Thêm dòng này để bảo mật thông tin giữa các giáo viên
            ->latest()
            ->get();

        return view('teacher.assignments.assign', compact('exam', 'classes', 'distributions'));
    }
    // 2. Xử lý lưu thông tin khi giáo viên bấm nút "Xác nhận giao bài"
    public function assignStore(Request $request, $examId)
    {
        $exam = Assignment::findOrFail($examId);

        // Bổ sung Ràng buộc Validate: Bắt buộc chọn buổi học và nhập thời gian làm bài
        $request->validate([
            'lesson_session_id' => ['required', 'exists:lesson_sessions,id'],
            'duration_minutes'  => ['required', 'integer', 'min:1'],
            'start_time'        => ['required', 'date'],
            'due_time'          => ['required', 'date', 'after:start_time'],
            'max_attempts'      => ['required', 'integer', 'min:0'],
        ], [
            'lesson_session_id.required' => 'Vui lòng chọn một buổi học cụ thể để giao bài (Bắt buộc).',
            'lesson_session_id.exists'   => 'Buổi học được chọn không hợp lệ.',
            'duration_minutes.required'  => 'Vui lòng nhập thời gian làm bài.',
            'duration_minutes.integer'   => 'Thời gian làm bài phải là một số nguyên dương.',
            'start_time.required'        => 'Vui lòng chọn thời gian bắt đầu mở bài.',
            'due_time.required'          => 'Vui lòng chọn thời gian hạn nộp bài.',
            'due_time.after'             => 'Thời gian hạn nộp phải diễn ra sau thời gian mở bài.',
            'max_attempts.required'      => 'Vui lòng nhập số lần làm bài tối đa.',
        ]);

        // Tạo bản ghi phân phối bài thi vào bảng dữ liệu điều phối bài tập
        $distribution = AssignmentDistribution::create([
            'assignment_id'    => $exam->id,
            'user_id'          => Auth::id(),
            'duration_minutes' => $request->input('duration_minutes'), // Nhận từ ô nhập liệu trên form
            'open_time'        => $request->input('start_time'), 
            'close_time'       => $request->input('due_time'),   
            'max_attempts'     => $request->input('max_attempts'),
            'status'           => 'active',
            'lesson_session_id'=> $request->input('lesson_session_id'), // Nhận ID buổi học từ form chọn
        ]);

        if ($distribution->open_time && $distribution->open_time->lte(now())) {
            app(NotificationService::class)->notifyAssignmentOpened($distribution);
        }

        return redirect()->route('teacher.exams.index')->with('success', 'Đã phân phối giao bài tập đến buổi học thành công!');
    }

    // 3. Hàm xóa/Hủy giao bài tập khỏi lớp học
    public function destroy($id)
    {
        $distribution = AssignmentDistribution::findOrFail($id);
        
        // Kiểm tra quyền sở hữu (Chỉ người giao hoặc Admin mới được xóa)
        if (Auth::user()->role !== 'admin' && $distribution->user_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền gỡ bài tập này.');
        }

        $distribution->delete();
        return redirect()->back()->with('success', 'Đã hủy giao bài tập này thành công!');
    }

    public function globalIndex(Request $request)
    {
        // 1. Tạo query gốc lấy phối bài của giáo viên hiện tại
        $query = AssignmentDistribution::with(['assignment', 'lessonSession.courseClass'])
            ->where('user_id', Auth::id());

        // 2. BỔ SUNG LOGIC: Nếu có class_id truyền lên từ sidebar, lọc chỉ lấy bài tập của riêng lớp đó
        $class = null;
        if ($request->has('class_id')) {
            $classId = $request->query('class_id');
            $class = \App\Models\CourseClass::find($classId);
            
            if ($class) {
                $query->whereHas('lessonSession', function ($q) use ($classId) {
                    $q->where('course_class_id', $classId);
                });
            }
        }

        // 3. Sắp xếp bài tập mới nhất lên đầu
        $distributions = $query->latest()->get();

        // Truyền cả $distributions và $class sang giao diện view
        return view('teacher.assignments.global_index', compact('distributions', 'class'));
    }
}
