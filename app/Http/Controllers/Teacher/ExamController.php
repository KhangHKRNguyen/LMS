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
        $request->validate([
            'title' => 'required|string|max:255',
            'assignment_type_id' => 'required|exists:assignment_types,id',
            'file_path' => 'nullable|file|mimes:mp3,wav,m4a,wma,aac|max:40960',
            'questions' => 'required|array|min:1',
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
                    $correctLetter = $qData['correct_option'] ?? null;
                    
                    foreach ($qData['options'] as $oData) {
                        $question->options()->create([
                            'option_letter'  => $oData['option_letter'],
                            'option_content' => $oData['option_content'],
                            'is_correct'     => ($oData['option_letter'] === $correctLetter), 
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

            // Xóa câu hỏi cũ để ghi đè danh sách đồng bộ mới
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
                    $correctLetter = $qData['correct_option'] ?? null;

                    foreach ($qData['options'] as $oData) {
                        $question->options()->create([
                            'option_letter'  => $oData['option_letter'],
                            'option_content' => $oData['option_content'],
                            'is_correct'     => ($oData['option_letter'] === $correctLetter),
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