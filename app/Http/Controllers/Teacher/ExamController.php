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
        $skills = Skill::all();
        return view('teacher.exams.create', compact('types', 'skills'));
    }

    public function store(Request $request)
    {
        // 1. Bổ sung validation chặt chẽ cho từng phần tử bên trong mảng câu hỏi
        $request->validate([
            'title' => 'required|string|max:255',
            'assignment_type_id' => 'required|exists:assignment_types,id',
            'file_path' => 'nullable|file|mimes:mp3,wav,m4a,wma,aac|max:40960',
            'questions' => 'required|array|min:1',
            'questions.*.question_type' => 'required|string', // Bắt buộc phải có loại câu hỏi
            'questions.*.skill_id' => 'required|exists:skills,id',
        ]);

        DB::transaction(function () use ($request) {
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

            // Thay vì dùng $index (dễ bị lỗi nếu key từ JS là chuỗi ngẫu nhiên), dùng biến đếm độc lập
            $qCounter = 1; 

            foreach ($request->input('questions', []) as $qData) {
                // Kiểm tra phòng vệ: Nếu thiếu loại câu hỏi thì bỏ qua để không gây lỗi 500
                if (!isset($qData['question_type'])) {
                    continue;
                }

                $question = $exam->questions()->create([
                    'question_number' => $qData['question_number'] ?? $qCounter,
                    'question_type' => $qData['question_type'],
                    'question_text' => $qData['question_text'] ?? null,
                    'points' => $qData['points'] ?? 1.00,
                    'max_recording_time' => $qData['max_recording_time'] ?? null,
                    'skill_id' => $qData['skill_id'],
                ]);

                // Xử lý câu hỏi trắc nghiệm
                if ($qData['question_type'] === 'trac_nghiem' && isset($qData['options'])) {
                    $correctLetter = $qData['correct_option'] ?? null;
                    
                    foreach ($qData['options'] as $oData) {
                        $question->options()->create([
                            'option_letter'  => $oData['option_letter'],
                            'option_content' => $oData['option_content'],
                            'is_correct'     => ($oData['option_letter'] === $correctLetter), 
                        ]);
                    }
                } 
                // Xử lý câu hỏi điền từ
                elseif ($qData['question_type'] === 'dien_tu' && isset($qData['keywords'])) {
                    // Sử dụng biến đếm ô trống độc lập, tránh ghi đè biến $index của vòng lặp ngoài
                    $kCounter = 1; 

                    foreach ($qData['keywords'] as $kData) {
                        $blankOrder = is_array($kData) ? ($kData['blank_order'] ?? $kCounter) : $kCounter;
                        $correctKeyword = is_array($kData) ? ($kData['correct_keyword'] ?? '') : $kData;

                        if (trim((string)$correctKeyword) === '') {
                            $kCounter++;
                            continue;
                        }

                        $question->keywords()->create([
                            'blank_order'     => $blankOrder,
                            'correct_keyword' => trim((string)$correctKeyword),
                        ]);

                        $kCounter++;
                    }
                }

                $qCounter++;
            }
        });

        return redirect()->route('teacher.exams.index')->with('success', 'Đã thêm đề thi mới vào ngân hàng đề thành công!');
    }

    public function show($id)
    {
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
        // 1. Áp dụng validation chặt chẽ tương tự hàm store
        $request->validate([
            'title' => 'required|string|max:255',
            'assignment_type_id' => 'required|exists:assignment_types,id',
            'file_path' => 'nullable|file|mimes:mp3,wav,m4a,wma,aac|max:40960',
            'questions' => 'required|array|min:1',
            'questions.*.question_type' => 'required|string',
            'questions.*.skill_id' => 'required|exists:skills,id',
        ]);

        $exam = Assignment::findOrFail($id);

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

            // Xóa an toàn câu hỏi cũ và các mối quan hệ ràng buộc trước khi nạp dữ liệu mới
            foreach ($exam->questions as $oldQuestion) {
                $oldQuestion->options()->delete();
                $oldQuestion->keywords()->delete();
                $oldQuestion->delete();
            }

            // Khởi tạo biến đếm câu hỏi độc lập
            $qCounter = 1; 

            foreach ($request->input('questions', []) as $qData) {
                // Kiểm tra phòng vệ tránh lỗi sập hệ thống (500)
                if (!isset($qData['question_type'])) {
                    continue;
                }

                $question = $exam->questions()->create([
                    'question_number' => $qData['question_number'] ?? $qCounter,
                    'question_type' => $qData['question_type'],
                    'question_text' => $qData['question_text'] ?? null,
                    'points' => $qData['points'] ?? 1.00,
                    'max_recording_time' => $qData['max_recording_time'] ?? null,
                    'skill_id' => $qData['skill_id'],
                ]);

                // Xử lý câu hỏi trắc nghiệm
                if ($qData['question_type'] === 'trac_nghiem' && isset($qData['options'])) {
                    $correctLetter = $qData['correct_option'] ?? null;

                    foreach ($qData['options'] as $oData) {
                        $question->options()->create([
                            'option_letter'  => $oData['option_letter'],
                            'option_content' => $oData['option_content'],
                            'is_correct'     => ($oData['option_letter'] === $correctLetter),
                        ]);
                    }
                } 
                // Xử lý câu hỏi điền từ (Đã đồng bộ code xử lý an toàn từ hàm store qua)
                elseif ($qData['question_type'] === 'dien_tu' && isset($qData['keywords'])) {
                    $kCounter = 1; 

                    foreach ($qData['keywords'] as $kData) {
                        $blankOrder = is_array($kData) ? ($kData['blank_order'] ?? $kCounter) : $kCounter;
                        $correctKeyword = is_array($kData) ? ($kData['correct_keyword'] ?? '') : $kData;

                        if (trim((string)$correctKeyword) === '') {
                            $kCounter++;
                            continue;
                        }

                        $question->keywords()->create([
                            'blank_order'     => $blankOrder,
                            'correct_keyword' => trim((string)$correctKeyword),
                        ]);

                        $kCounter++;
                    }
                }

                $qCounter++;
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