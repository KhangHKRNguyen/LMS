<?php

namespace App\Http\Controllers\Teacher;

use App\Models\Assignment;
use App\Models\CourseClass;
use App\Services\ExcelImportService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class AssignmentController extends Controller
{
    public function index(Request $request)
    {
        // Lấy class_id từ URL (?class_id=1)
        $classId = $request->query('class_id');

        // Tìm lớp học cụ thể và nạp kèm bài tập, đếm sĩ số để nuôi Sidebar
        $class = CourseClass::with(['assignments' => function($q) {
            $q->with('exam');
        }])
        ->withCount('students')
        ->findOrFail($classId);

        return view('teacher.assignments.index', compact('class'));
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

    public function parseImport(Request $request, ExcelImportService $excelImportService)
    {
        try {
            $request->validate([
                'import_file' => ['required', 'file', 'max:5120'],
            ]);
            
            $questions = $excelImportService->importQuestions($request->file('import_file'));
            
            return response()->json([
                'success' => true,
                'questions' => $questions,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->validator->errors()->first('import_file'),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi xử lý file hoặc cấu trúc Excel/CSV không đúng định dạng mẫu.',
            ], 500);
        }
    }

    public function export(Assignment $assignment)
    {
        $this->authorizeAssignment($assignment);
        $questions = $assignment->questions()->get();
        
        $csvContent = "\xEF\xBB\xBF"; 
        $csvContent .= "question_text,option_a,option_b,option_c,option_d,correct_option\n";
        
        foreach ($questions as $question) {
            $text = str_replace('"', '""', $question->question_text);
            $a = str_replace('"', '""', $question->option_a);
            $b = str_replace('"', '""', $question->option_b);
            $c = str_replace('"', '""', $question->option_c);
            $d = str_replace('"', '""', $question->option_d);
            $correct = $question->correct_option;
            
            $csvContent .= "\"{$text}\",\"{$a}\",\"{$b}\",\"{$c}\",\"{$d}\",\"{$correct}\"\n";
        }
        
        $filename = "danh_sach_cau_hoi_" . Str::slug($assignment->title) . ".csv";
        
        return response($csvContent, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function downloadAttachment(Assignment $assignment)
    {
        $this->authorizeAssignment($assignment);
        
        if (!$assignment->file_path || !Storage::disk('public')->exists($assignment->file_path)) {
            return redirect()->back()->with('error', 'Không tìm thấy file đề bài đính kèm.');
        }
        
        $pathInfo = pathinfo($assignment->file_path);
        $extension = $pathInfo['extension'] ?? 'bin';
        $filename = "de_bai_" . Str::slug($assignment->title) . "." . $extension;
        
        return Storage::disk('public')->download($assignment->file_path, $filename);
    }

    public function template(ExcelImportService $excelImportService)
    {
        return response($excelImportService->sampleCsvContent(), 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="mau_import_cau_hoi.csv"',
        ]);
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
}