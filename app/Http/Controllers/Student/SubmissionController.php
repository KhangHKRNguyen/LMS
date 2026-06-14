<?php
use App\Services\IeltsScoreService;
use App\Models\Submission;

public function submitExam(Request $request, $assignmentDistributionId)
{
    // 1. Giả sử bạn lấy ra danh sách câu hỏi của đề thi để check đáp án
    // Logic đếm số câu đúng của bạn...
    $correctReading = 7; 
    $totalReading = 10;  // Đếm bằng code: $exam->questions()->where('skill_id', 2)->count();

    $correctListening = 4;
    $totalListening = 5; // Đếm bằng code: $exam->questions()->where('skill_id', 1)->count();

    // 2. Sử dụng Service để tính điểm Band thẳng từ số câu đúng
    $readingBand = IeltsScoreService::calculateSkillBand($correctReading, $totalReading);
    $listeningBand = IeltsScoreService::calculateSkillBand($correctListening, $totalListening);

    // 3. Tạo mới bài nộp (Submission) và lưu điểm Nghe/Đọc vào DB
    // Vì Writing/Speaking phải chờ giáo viên nên lúc này ta để trống (null)
    Submission::create([
        'submission_time' => now(),
        'listening_grade' => $listeningBand, // Đạt 7.00 hoặc 7.50 tự động
        'reading_grade' => $readingBand,
        'writing_grade' => null, 
        'speaking_grade' => null,
        'total_grade' => null, // Chưa có overall vì chưa chấm xong 4 kỹ năng
        'status' => 'submitted',
        'user_id' => auth()->id(),
        'assignment_distribution_id' => $assignmentDistributionId
    ]);

    return redirect()->back()->with('success', 'Nộp bài thành công! Chờ giáo viên chấm Writing/Speaking.');
}

public function gradeSubmission(Request $request, $submissionId)
{
    $submission = Submission::findOrFail($submissionId);

    // 1. Lấy điểm giáo viên chấm từ Form nhập liệu (ví dụ giáo viên nhập thẳng: 6.5 và 7.0)
    $writingGrade = (float) $request->input('writing_grade');
    $speakingGrade = (float) $request->input('speaking_grade');

    // 2. Lấy lại điểm Listening và Reading đã được hệ thống chấm tự động trước đó
    $listeningGrade = (float) $submission->listening_grade;
    $readingGrade = (float) $submission->reading_grade;

    // 3. Tính điểm Overall tự động làm tròn bằng Service
    $overallGrade = IeltsScoreService::calculateOverall(
        $listeningGrade,
        $readingGrade,
        $writingGrade,
        $speakingGrade
    );

    // 4. Cập nhật lại vào Database
    $submission->update([
        'writing_grade' => $writingGrade,
        'speaking_grade' => $speakingGrade,
        'total_grade' => $overallGrade, // Điểm Overall chuẩn IELTS (.0 hoặc .5)
        'status' => 'graded', // Chuyển trạng thái sang Đã chấm xong
        'teacher_comment' => $request->input('teacher_comment')
    ]);

    return redirect()->back()->with('success', 'Đã lưu điểm và tính Overall thành công!');
}