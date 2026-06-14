<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UserAndClassSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ==========================================
        // 1. SEED BẢNG VAI TRÒ (ROLES) - ĐỦ 4 ROLE CỦA HỆ THỐNG
        // ==========================================
        DB::table('roles')->insert([
            ['id' => 1, 'name' => 'admin', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'teacher', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => 'assistant', 'created_at' => now(), 'updated_at' => now()], // Trợ lý lớp học (TA)
            ['id' => 4, 'name' => 'student', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // ==========================================
        // 2. SEED BẢNG KỸ NĂNG (SKILLS)
        // ==========================================
        DB::table('skills')->insert([
            ['id' => 1, 'name' => 'Listening', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'Reading', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => 'Writing', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'name' => 'Speaking', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // ==========================================
        // 3. SEED BẢNG TRÌNH ĐỘ (QUALIFICATIONS)
        // ==========================================
        DB::table('qualifications')->insert([
            ['id' => 1, 'expert_level' => 'IELTS 7.5 Certificate', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'expert_level' => 'IELTS 8.5 Master', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // ==========================================
        // 4. SEED BẢNG LOẠI BÀI TẬP (ASSIGNMENT_TYPES)
        // ==========================================
        DB::table('assignment_types')->insert([
            ['id' => 1, 'name' => 'Homework', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'On-class', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => 'Mid-term', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'name' => 'Final', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // ==========================================
        // 5. SEED BẢNG NGƯỜI DÙNG & TÀI KHOẢN (USERS) - 4 ACCOUNT CHO 4 ROLE
        // ==========================================
        // Mật khẩu đồng bộ cho tất cả tài khoản: 12345678
        DB::table('users')->insert([
            [
                'id' => 1,
                'name' => 'Quản trị viên Hệ thống',
                'gender' => 'Nam',
                'dob' => '1995-01-01',
                'phone' => '0912345678',
                'avatar' => null,
                'email' => 'admin@gmail.com',
                'password' => Hash::make('12345678'),
                'status' => 'active',
                'role_id' => 1, // Admin
                'qualification_id' => null,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'id' => 2,
                'name' => 'Thầy Nguyễn Học Anh',
                'gender' => 'Nam',
                'dob' => '1990-05-15',
                'phone' => '0988888888',
                'avatar' => null,
                'email' => 'giangvien@gmail.com',
                'password' => Hash::make('12345678'),
                'status' => 'active',
                'role_id' => 2, // Teacher
                'qualification_id' => 2,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'id' => 3,
                'name' => 'Cô Lê Trợ Lý (TA)',
                'gender' => 'Nữ',
                'dob' => '2001-08-25',
                'phone' => '0966666666',
                'avatar' => null,
                'email' => 'troly@gmail.com',
                'password' => Hash::make('12345678'),
                'status' => 'active',
                'role_id' => 3, // Assistant (TA)
                'qualification_id' => 1,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'id' => 4,
                'name' => 'Trần Văn Học Viên',
                'gender' => 'Nam',
                'dob' => '2004-10-20',
                'phone' => '0977777777',
                'avatar' => null,
                'email' => 'hocviena@gmail.com',
                'password' => Hash::make('12345678'),
                'status' => 'active',
                'role_id' => 4, // Student
                'qualification_id' => null,
                'created_at' => now(), 'updated_at' => now()
            ]
        ]);

        // ==========================================
        // 6. SEED KHÓA HỌC & LỚP HỌC (COURSES & CLASSES)
        // ==========================================
        DB::table('courses')->insert([
            'id' => 1,
            'name' => 'Khóa luyện thi IELTS Chuyên Sâu 6.5+',
            'description' => 'Khóa học bứt tốc giúp học viên nắm vững kỹ năng xử lý cả 4 đề thi IELTS.',
            'output_target' => 'Cam kết đầu ra tối thiểu IELTS Band 6.5',
            'duration' => '48',
            'created_at' => now(), 'updated_at' => now()
        ]);

        DB::table('course_classes')->insert([
            'id' => 1,
            'class_name' => 'Lớp IELTS-6.5-K20',
            'start_date' => Carbon::now()->format('Y-m-d'),
            'end_date' => Carbon::now()->addMonths(3)->format('Y-m-d'),
            'room' => 'Phòng Lab 202',
            'status' => 'active',
            'course_id' => 1,
            'created_at' => now(), 'updated_at' => now()
        ]);

        // Gán Giảng viên (2), Trợ lý (3) và Học viên (4) vào lớp (1)
        DB::table('class_user')->insert([
            ['course_class_id' => 1, 'user_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['course_class_id' => 1, 'user_id' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['course_class_id' => 1, 'user_id' => 4, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // ==========================================
        // 7. SEED THÔNG BÁO (NOTIFICATIONS & RECIPIENTS)
        // ==========================================
        DB::table('notifications')->insert([
            'id' => 1,
            'title' => 'Chào mừng đến với lớp học mới!',
            'content' => 'Hệ thống đã thêm bạn vào lớp IELTS-6.5-K20. Hãy kiểm tra lịch học nhé.',
            'sender_id' => 1, // Admin gửi
            'created_at' => now(), 'updated_at' => now()
        ]);

        DB::table('notification_recipients')->insert([
            ['notification_id' => 1, 'user_id' => 4, 'is_read' => false, 'created_at' => now(), 'updated_at' => now()], // Gửi cho học viên
            ['notification_id' => 1, 'user_id' => 2, 'is_read' => true, 'created_at' => now(), 'updated_at' => now()],  // Gửi bản copy cho GV
        ]);

        // ==========================================
        // 8. SEED BUỔI HỌC & CHUYÊN CẦN (LESSON_SESSIONS, ATTENDANCES, LEAVE_REQUESTS)
        // ==========================================
        // Tạo 2 buổi học (Buổi 1 đã diễn ra, Buổi 2 sắp diễn ra)
        DB::table('lesson_sessions')->insert([
            ['id' => 1, 'lesson_date' => Carbon::now()->subDays(2)->format('Y-m-d'), 'course_class_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'lesson_date' => Carbon::now()->addDays(2)->format('Y-m-d'), 'course_class_id' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Trợ lý hoặc giảng viên điểm danh buổi 1: Học viên đi học đầy đủ ('present')
        DB::table('attendances')->insert([
            'id' => 1,
            'status' => 'present', 
            'user_id' => 4, // Học viên
            'lesson_session_id' => 1,
            'created_at' => now(), 'updated_at' => now()
        ]);

        // Học viên làm đơn xin nghỉ trước cho buổi học số 2
        DB::table('leave_requests')->insert([
            'id' => 1,
            'reason' => 'Em có lịch thi học kỳ trùng vào ngày học này, mong thầy cô cho phép em xem lại record sau ạ.',
            'status' => 'pending', // Đang chờ duyệt
            'user_id' => 4,
            'lesson_session_id' => 2,
            'created_at' => now(), 'updated_at' => now()
        ]);

        // ==========================================
        // 9. SEED TÀI LIỆU LỚP HỌC (DOCUMENTS)
        // ==========================================
        DB::table('documents')->insert([
            'id' => 1,
            'title' => 'Cẩm nang chiến thuật IELTS Reading & Listening Band 7.0+',
            'file_path' => 'documents/cam_nang_ielts_65.pdf',
            'course_class_id' => 1,
            'user_id' => 2, // Do giảng viên tải lên
            'created_at' => now(), 'updated_at' => now()
        ]);

        // ==========================================
        // 10. SEED ĐỀ THI MẪU & PHÂN PHỐI BÀI TẬP (ASSIGNMENTS)
        // ==========================================
        DB::table('assignments')->insert([
            'id' => 1,
            'title' => 'IELTS Mini Test - Đề tổng hợp 4 kỹ năng số 01',
            'description' => 'Bài kiểm tra tiến độ định kỳ 4 kỹ năng chuẩn cấu trúc IELTS.',
            'file_path' => null,
            'assignment_type_id' => 2, // Mini Test
            'user_id' => 2,
            'created_at' => now(), 'updated_at' => now()
        ]);

        DB::table('assignment_distributions')->insert([
            'id' => 1,
            'duration_minutes' => 60,
            'open_time' => now(),
            'close_time' => Carbon::now()->addDays(7),
            'max_attempts' => 2,
            'status' => 'Đang mở',
            'lesson_session_id' => 1,
            'assignment_id' => 1,
            'user_id' => 2,
            'created_at' => now(), 'updated_at' => now()
        ]);

        // ==========================================
        // 11. SEED CÂU HỎI & ĐÁP ÁN GỐC (QUESTIONS, OPTIONS, KEYWORDS)
        // ==========================================
        // Câu 1: Reading (Trắc nghiệm)
        DB::table('questions')->insert([
            'id' => 1, 'question_number' => 1, 'question_type' => 'trac_nghiem',
            'question_text' => 'According to paragraph 2, what is the main reason for global warming?',
            'points' => 2.50, 'max_recording_time' => null, 'assignment_id' => 1, 'skill_id' => 2, 'created_at' => now(), 'updated_at' => now()
        ]);
        DB::table('question_options')->insert([
            ['id' => 1, 'option_letter' => 'A', 'option_content' => 'Deforestation.', 'is_correct' => false, 'question_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'option_letter' => 'B', 'option_content' => 'Excessive CO2 emissions.', 'is_correct' => true, 'question_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'option_letter' => 'C', 'option_content' => 'Solar cycles.', 'is_correct' => false, 'question_id' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Câu 2: Listening (Điền từ)
        DB::table('questions')->insert([
            'id' => 2, 'question_number' => 2, 'question_type' => 'dien_tu',
            'question_text' => 'The facility opens in [ô trống 1] and fee is [ô trống 2] dollars.',
            'points' => 2.50, 'max_recording_time' => null, 'assignment_id' => 1, 'skill_id' => 1, 'created_at' => now(), 'updated_at' => now()
        ]);
        DB::table('question_keywords')->insert([
            ['id' => 1, 'blank_order' => 1, 'correct_keyword' => 'October', 'question_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'blank_order' => 2, 'correct_keyword' => '15', 'question_id' => 2, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Câu 3: Writing (Tự luận)
        DB::table('questions')->insert([
            'id' => 3, 'question_number' => 3, 'question_type' => 'writing',
            'question_text' => 'Discuss the impacts of technology on traditional education.',
            'points' => 2.50, 'max_recording_time' => null, 'assignment_id' => 1, 'skill_id' => 3, 'created_at' => now(), 'updated_at' => now()
        ]);

        // Câu 4: Speaking (Ghi âm)
        DB::table('questions')->insert([
            'id' => 4, 'question_number' => 4, 'question_type' => 'speaking',
            'question_text' => 'Describe a beautiful place you have visited.',
            'points' => 2.50, 'max_recording_time' => 120, 'assignment_id' => 1, 'skill_id' => 4, 'created_at' => now(), 'updated_at' => now()
        ]);

        // ==========================================
        // 12. SEED BÀI NỘP CỦA HỌC VIÊN (SUBMISSIONS) - CHỨA ĐẦY ĐỦ 4 CỘT ĐIỂM THÀNH PHẦN
        // ==========================================
        DB::table('submissions')->insert([
            'id' => 1,
            'submission_time' => now(),
            // Điểm thành phần chi tiết của 4 kỹ năng IELTS
            'listening_grade' => 7.50,
            'reading_grade' => 8.00,
            'writing_grade' => 6.00,
            'speaking_grade' => 6.50,
            'total_grade' => 7.00, // Điểm Overall trung bình cộng làm tròn theo quy chế IELTS
            'teacher_comment' => 'Kỹ năng Đọc và Nghe rất tốt. Bài viết cần trau chuốt thêm từ vựng học thuật. Phát âm tự nhiên.',
            'attempt_number' => 1,
            'status' => 'graded', // Đã chấm điểm
            'user_id' => 4, // Học viên nộp
            'assignment_distribution_id' => 1,
            'created_at' => now(), 'updated_at' => now()
        ]);

        // ==========================================
        // 13. SEED CHI TIẾT ĐÁP ÁN BÀI LÀM CỦA HỌC VIÊN CHO TỪNG DẠNG
        // ==========================================
        // Trắc nghiệm (Chọn đáp án trúng B là đáp án đúng)
        DB::table('answers_multiple_choice')->insert([
            'id' => 1,
            'submission_id' => 1, 'question_id' => 1, 'question_option_id' => 2,
            'created_at' => now(), 'updated_at' => now()
        ]);

        // Điền từ (Học viên điền đúng 2 từ khóa)
        DB::table('answers_fill_blank')->insert([
            'id' => 1, 'submission_id' => 1, 'question_id' => 2, 'created_at' => now(), 'updated_at' => now()
        ]);
        DB::table('answer_fill_blank_details')->insert([
            ['answer_fill_blank_id' => 1, 'blank_order' => 1, 'student_input' => 'October', 'created_at' => now(), 'updated_at' => now()],
            ['answer_fill_blank_id' => 1, 'blank_order' => 2, 'student_input' => '15', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Bài viết luận (Writing)
        DB::table('answers_writing')->insert([
            'id' => 1,
            'essay_content' => 'In recent years, technology has revolutionized the education system in various ways...',
            'submission_id' => 1, 'question_id' => 3,
            'created_at' => now(), 'updated_at' => now()
        ]);

        // File ghi âm âm thanh (Speaking)
        DB::table('answers_speaking')->insert([
            'id' => 1,
            'audio_file_path' => 'audio/submissions/student_4_q4.mp3',
            'submission_id' => 1, 'question_id' => 4,
            'created_at' => now(), 'updated_at' => now()
        ]);

        // ==========================================
        // 14. SEED PHẢN HỒI & LỊCH SỬ SỬA ĐIỂM (FEEDBACKS & GRADE_HISTORIES)
        // ==========================================
        DB::table('feedbacks')->insert([
            'id' => 1,
            'content' => 'Thầy đánh giá rất cao sự tiến bộ của em ở bài Mock Test lần này, cố gắng phát huy!',
            'submission_id' => 1,
            'user_id' => 2, // Giảng viên phản hồi
            'created_at' => now(), 'updated_at' => now()
        ]);

        DB::table('grade_histories')->insert([
            'id' => 1,
            'old_grade' => 6.00, // Điểm trước đó
            'new_grade' => 7.00,  // Điểm sau khi lưu
            'reason' => 'Giảng viên hoàn thành chấm điểm thi thử lần 2',
            'submission_id' => 1,
            'question_id' => 1,
            'user_id' => 2, // Người thực hiện chấm/sửa
            'created_at' => now(), 'updated_at' => now()
        ]);

        // ==========================================
        // 15. SEED BẢNG KẾT QUẢ TỔNG HỢP (LEARNING_RESULTS)
        // ==========================================
        DB::table('learning_results')->insert([
            'id' => 1,
            'user_id' => 4,
            'course_class_id' => 1,
            
            // ĐỔI THÀNH CÁC CỘT CÓ TRONG MIGRATION CỦA BẠN
            'midterm_grade' => 7.00,  // Điểm giữa khóa
            'final_grade' => 8.00,    // Điểm cuối khóa (nullable, có thể truyền hoặc bỏ qua)
            'approved_date' => now()->toDateString(), 
            'approval_status' => 'Đã duyệt', 
            
            'created_at' => now(), 
            'updated_at' => now()
        ]);
    }
}