<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentType;
use App\Models\Skill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ExamController extends Controller
{
    public function index()
    {
        $exams = Assignment::with(['assignmentType', 'user'])
            ->withCount('questions')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('teacher.exams.index', compact('exams'));
    }

    public function create()
    {
        $types = AssignmentType::all();
        $skills = Skill::all(); // Lấy danh sách Nghe, Nói, Đọc, Viết để giáo viên cấu hình từng câu hỏi
        return view('teacher.exams.create', compact('types', 'skills'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'assignment_type_id' => 'required|exists:assignment_types,id',
            'file_path' => 'nullable|file|mimes:pdf,doc,docx,zip|max:10240',
            'questions' => 'required|array|min:1',
        ]);

        DB::transaction(function () use ($request) {
            // 1. Tạo đề thi gốc
            $exam = Assignment::create([
                'title' => $request->title,
                'description' => $request->description,
                'assignment_type_id' => $request->assignment_type_id,
                'user_id' => Auth::id(),
            ]);

            if ($request->hasFile('file_path')) {
                $exam->file_path = $request->file('file_path')->store('exams', 'public');
                $exam->save();
            }

            // 2. Lưu danh sách câu hỏi lồng nhau
            foreach ($request->input('questions', []) as $index => $qData) {
                $question = $exam->questions()->create([
                    'question_number' => $qData['question_number'] ?? ($index + 1),
                    'question_type' => $qData['question_type'],
                    'question_text' => $qData['question_text'] ?? null,
                    'points' => $qData['points'] ?? 1.00,
                    'max_recording_time' => $qData['max_recording_time'] ?? null,
                    'skill_id' => $qData['skill_id'],
                ]);

                // Xử lý các nhánh con phụ thuộc dạng câu hỏi
                if ($qData['question_type'] === 'trac_nghiem' && isset($qData['options'])) {
                    foreach ($qData['options'] as $oData) {
                        $question->options()->create([
                            'option_letter' => $oData['option_letter'],
                            'option_content' => $oData['option_content'],
                            'is_correct' => isset($oData['is_correct']) && $oData['is_correct'] == '1',
                        ]);
                    }
                } elseif ($qData['question_type'] === 'dien_tu' && isset($qData['keywords'])) {
                    foreach ($qData['keywords'] as $kData) {
                        $question->keywords()->create([
                            'blank_order' => $kData['blank_order'],
                            'correct_keyword' => $kData['correct_keyword'],
                        ]);
                    }
                }
            }
        });

        return redirect()->route('teacher.exams.index')->with('success', 'Đã thêm đề thi mới vào ngân hàng đề thành công!');
    }

    public function show($id)
    {
        // Xem chi tiết cấu trúc toàn bộ đề và đáp án chuẩn của từng câu hỏi
        $exam = Assignment::with(['assignmentType', 'questions.skill', 'questions.options', 'questions.keywords'])->findOrFail($id);
        return view('teacher.exams.show', compact('exam'));
    }

    public function edit($id)
    {
        $exam = Assignment::with(['questions.options', 'questions.keywords'])->findOrFail($id);
        $types = AssignmentType::all();
        $skills = Skill::all();
        return view('teacher.exams.edit', compact('exam', 'types', 'skills'));
    }

    public function update(Request $request, $id)
    {
        $exam = Assignment::findOrFail($id);
        
        $request->validate([
            'title' => 'required|string|max:255',
            'assignment_type_id' => 'required|exists:assignment_types,id',
            'questions' => 'required|array|min:1',
        ]);

        DB::transaction(function () use ($request, $exam) {
            $exam->update([
                'title' => $request->title,
                'description' => $request->description,
                'assignment_type_id' => $request->assignment_type_id,
            ]);

            if ($request->hasFile('file_path')) {
                if ($exam->file_path) {
                    Storage::disk('public')->delete($exam->file_path);
                }
                $exam->file_path = $request->file('file_path')->store('exams', 'public');
                $exam->save();
            }

            // Giải pháp tối ưu nhất cho cấu trúc lồng nhau phức tạp trên giao diện Blade cũ:
            // Xóa sạch các câu hỏi cũ (Hệ thống tự động cascade xóa options/keywords) và ghi đè lại loạt mới
            $exam->questions()->delete();

            foreach ($request->input('questions', []) as $index => $qData) {
                $question = $exam->questions()->create([
                    'question_number' => $qData['question_number'] ?? ($index + 1),
                    'question_type' => $qData['question_type'],
                    'question_text' => $qData['question_text'] ?? null,
                    'points' => $qData['points'] ?? 1.00,
                    'max_recording_time' => $qData['max_recording_time'] ?? null,
                    'skill_id' => $qData['skill_id'],
                ]);

                if ($qData['question_type'] === 'trac_nghiem' && isset($qData['options'])) {
                    foreach ($qData['options'] as $oData) {
                        $question->options()->create([
                            'option_letter' => $oData['option_letter'],
                            'option_content' => $oData['option_content'],
                            'is_correct' => isset($oData['is_correct']) && $oData['is_correct'] == '1',
                        ]);
                    }
                } elseif ($qData['question_type'] === 'dien_tu' && isset($qData['keywords'])) {
                    foreach ($qData['keywords'] as $kData) {
                        $question->keywords()->create([
                            'blank_order' => $kData['blank_order'],
                            'correct_keyword' => $kData['correct_keyword'],
                        ]);
                    }
                }
            }
        });

        return redirect()->route('teacher.exams.index')->with('success', 'Cập nhật đề thi thành công!');
    }

    public function destroy($id)
    {
        $exam = Assignment::findOrFail($id);
        if ($exam->file_path) {
            Storage::disk('public')->delete($exam->file_path);
        }
        $exam->delete();
        return redirect()->route('teacher.exams.index')->with('success', 'Đã xóa đề thi khỏi ngân hàng đề.');
    }
}