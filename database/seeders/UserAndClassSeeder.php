<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserAndClassSeeder extends Seeder
{
    public function run(): void
    {
        // 1. ĐỔ DỮ LIỆU BẢNG: users (Cập nhật thêm thông tin profile cá nhân)
        DB::table('users')->insert([
            [
                'id' => 1,
                'name' => 'Nguyễn Admin',
                'email' => 'admin@gmail.com',
                'password' => Hash::make('12345678'),
                'role' => 'admin',
                'status' => 'active',
                'gender' => 'Nam', 'birthday' => '1995-05-20', 'phone' => '0912345678', 'qualification' => 'Manager',
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'id' => 2,
                'name' => 'Trần Giảng Viên',
                'email' => 'giangvien@gmail.com',
                'password' => Hash::make('12345678'),
                'role' => 'teacher',
                'status' => 'active',
                'gender' => 'Nam', 'birthday' => '1988-10-15', 'phone' => '0988888888', 'qualification' => 'IELTS 8.5',
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'id' => 3,
                'name' => 'Lê Học Viên A',
                'email' => 'hocviena@gmail.com',
                'password' => Hash::make('12345678'),
                'role' => 'student',
                'status' => 'active',
                'gender' => 'Nữ', 'birthday' => '2002-01-01', 'phone' => '0977777777', 'qualification' => 'Target 7.0',
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'id' => 4,
                'name' => 'Phạm Học Viên B',
                'email' => 'hocvienb@gmail.com',
                'password' => Hash::make('12345678'),
                'role' => 'student',
                'status' => 'active',
                'gender' => 'Nam', 'birthday' => '2001-08-24', 'phone' => '0966666666', 'qualification' => 'Target 6.5',
                'created_at' => now(), 'updated_at' => now()
            ]
        ]);

        // 2. ĐỔ DỮ LIỆU BẢNG: courses (Danh mục khóa học tổng thể trước khi mở lớp)
        DB::table('courses')->insert([
            ['id' => 1, 'name' => 'IELTS Masterclass 7.0+', 'description' => 'Khóa học đột phá band điểm nâng cao', 'output_target' => '7.0', 'duration' => '3 tháng', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'IELTS Intensive Reading & Listening', 'description' => 'Tập trung chuyên sâu 2 kỹ năng nghe đọc', 'output_target' => '6.5', 'duration' => '2 tháng', 'created_at' => now(), 'updated_at' => now()]
        ]);

        // 3. ĐỔ DỮ LIỆU BẢNG: course_classes
        DB::table('course_classes')->insert([
            [
                'id' => 1,
                'class_name' => 'Luyện Writing',
                'start_time' => now(),
                'end_time' => now()->addMonths(3),
                'room' => 'Phòng 402-A2',
                'course_id' => 1,
                'status' => 'Đang mở',
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'id' => 2,
                'class_name' => 'Luyện giao tiếp căn bản',
                'start_time' => now(),
                'end_time' => now()->addMonths(3),
                'room' => 'Phòng 301-B1',
                'course_id' => 2,
                'status' => 'Đang mở',
                'created_at' => now(), 'updated_at' => now()
            ]
        ]);

        // 4. ĐỔ DỮ LIỆU BẢNG TRUNG GIAN: class_user (Gán người dùng vào lớp)
        DB::table('class_user')->insert([
            ['course_class_id' => 1, 'user_id' => 2, 'created_at' => now(), 'updated_at' => now()], 
            ['course_class_id' => 1, 'user_id' => 3, 'created_at' => now(), 'updated_at' => now()], 
            ['course_class_id' => 1, 'user_id' => 4, 'created_at' => now(), 'updated_at' => now()], 
            ['course_class_id' => 2, 'user_id' => 2, 'created_at' => now(), 'updated_at' => now()], 
            ['course_class_id' => 2, 'user_id' => 3, 'created_at' => now(), 'updated_at' => now()], 
        ]);

        // 5. ĐỔ DỮ LIỆU BẢNG: lesson_sessions (Tạo các buổi học thực tế để quản lý chuyên sâu)
        DB::table('lesson_sessions')->insert([
            ['id' => 1, 'course_class_id' => 1, 'session_date' => now()->subDays(1)->format('Y-m-d'), 'attendance_status' => 'Đã điểm danh', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'course_class_id' => 1, 'session_date' => now()->format('Y-m-d'), 'attendance_status' => 'Chưa điểm danh', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 6. ĐỔ DỮ LIỆU BẢNG: leave_requests (Đơn nghỉ học gắn liền với buổi học cụ thể)
        DB::table('leave_requests')->insert([
            [
                'id' => 1,
                'request_date' => now()->format('Y-m-d'),
                'reason' => 'Em bị ốm phải đi khám bệnh, xin phép thầy cho em nghỉ ạ.',
                'lesson_session_id' => 1,
                'user_id' => 3, 
                'receiver_id' => 1,
                'file_path' => 'uploads/evidence/giay_kham_benh.pdf',
                'status' => 'Đã duyệt',
                'created_at' => now(), 'updated_at' => now()
            ]
        ]);

        // 7. ĐỔ DỮ LIỆU BẢNG: attendances (Điểm danh thuộc về buổi học số 1)
        DB::table('attendances')->insert([
            ['id' => 1, 'attendance_date' => now()->subDays(1)->format('Y-m-d'), 'status' => 'Có mặt', 'lesson_session_id' => 1, 'user_id' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'attendance_date' => now()->subDays(1)->format('Y-m-d'), 'status' => 'Muộn', 'lesson_session_id' => 1, 'user_id' => 4, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 8. ĐỔ DỮ LIỆU BẢNG: materials (Tài liệu học tập)
        DB::table('materials')->insert([
            [
                'id' => 1,
                'title' => 'Slide Chương 1: Tổng quan về IELTS',
                'file_path' => 'uploads/materials/slide1.pdf',
                'course_class_id' => 1,
                'created_at' => now(), 'updated_at' => now()
            ]
        ]);

        // 9. ĐỔ DỮ LIỆU BẢNG: assignments (Tích hợp các trường IELTS Arena nâng cao)
        DB::table('assignments')->insert([
            [
                'id' => 1,
                'title' => 'IELTS Mock Test Full 4 Skills - Khảo sát chất lượng Tháng 10',
                'content' => 'Bài thi tổng hợp kiểm tra toàn diện năng lực học viên bao gồm đầy đủ các định dạng câu hỏi Nghe, Nói, Đọc, Viết.',
                'type' => 'Full Test',
                'skill' => 'all', // Đầy đủ kỹ năng
                'audio_path' => 'uploads/audio/full_mock_test_2026.mp3', // File nghe tổng cho phần Listening
                'passage' => 'The Evolution of Digital Education in 21st Century...', // Văn bản tổng hoặc cấu trúc đề
                'open_time' => now(),
                'due_time' => now()->addDays(7),
                'duration_minutes' => 180, // 180 phút làm bài liên tục
                'course_class_id' => 1,
                'is_visible' => true,
                'created_at' => now(), 'updated_at' => now()
            ]
        ]);

        // 10. ĐỔ DỮ LIỆU BẢNG: questions (Gom nhóm câu hỏi theo chuẩn IELTS Part)
        DB::table('questions')->insert([
            // DẠNG 1: LISTENING - TRẮC NGHIỆM (MCQ)
            [
                'id' => 1,
                'question_text' => 'Theo file nghe Listening Section 1, người đàn ông muốn đặt lịch hẹn vào ngày thứ mấy?',
                'option_a' => 'Monday', 'option_b' => 'Wednesday', 'option_c' => 'Friday', 'option_d' => 'Saturday',
                'correct_option' => 'B',
                'type' => 'Single Choice',
                'assignment_id' => 1,
                'question_group' => 'Listening - Section 1',
                'passage' => null, 'audio_path' => null,
                'created_at' => now(), 'updated_at' => now()
            ],
            // DẠNG 2: READING - TRUE / FALSE / NOT GIVEN
            [
                'id' => 2,
                'question_text' => 'Do các trường đại học trực tuyến phát triển, các trường đại học truyền thống sẽ biến mất hoàn toàn vào năm 2030.',
                'option_a' => 'True', 'option_b' => 'False', 'option_c' => 'Not Given', 'option_d' => null,
                'correct_option' => 'B',
                'type' => 'TFNG',
                'assignment_id' => 1,
                'question_group' => 'Reading - Passage 1',
                'passage' => 'While online learning platforms have surged in popularity, traditional institutions continue to adapt and thrive, debunking the myth of their total extinction.', 
                'audio_path' => null,
                'created_at' => now(), 'updated_at' => now()
            ],
            // DẠNG 3: READING - ĐIỀN TỪ VÀO CHỖ TRỐNG (Gap-filling)
            [
                'id' => 3,
                'question_text' => 'Học viên trực tuyến cần có tinh thần tự giác cao vì họ không có sự giám sát trực tiếp từ _______.',
                'option_a' => null, 'option_b' => null, 'option_c' => null, 'option_d' => null,
                'correct_option' => 'teachers', // Từ khóa đúng lưu ở đây để hệ thống tự check
                'type' => 'Fill in the blank',
                'assignment_id' => 1,
                'question_group' => 'Reading - Passage 1',
                'passage' => 'Distance learners must cultivate high levels of self-discipline, given the absence of physical enforcement from teachers.',
                'audio_path' => null,
                'created_at' => now(), 'updated_at' => now()
            ],
            // DẠNG 4: WRITING TASK 1 (Tự luận viết - Mô tả biểu đồ)
            [
                'id' => 4,
                'question_text' => 'The chart below shows the percentage of households with internet access in different regions between 2015 and 2025. Summarize the information...',
                'option_a' => null, 'option_b' => null, 'option_c' => null, 'option_d' => null,
                'correct_option' => null,
                'type' => 'Writing Task 1',
                'assignment_id' => 1,
                'question_group' => 'Writing Section',
                'passage' => '[Link ảnh biểu đồ: uploads/images/writing_task1_chart.png]', // Prompt đề bài viết
                'audio_path' => null,
                'created_at' => now(), 'updated_at' => now()
            ],
            // DẠNG 5: WRITING TASK 2 (Tự luận viết - Essay nghị luận)
            [
                'id' => 5,
                'question_text' => 'Some people believe that artificial intelligence will completely replace human teachers in the future. To what extent do you agree or disagree?',
                'option_a' => null, 'option_b' => null, 'option_c' => null, 'option_d' => null,
                'correct_option' => null,
                'type' => 'Writing Task 2',
                'assignment_id' => 1,
                'question_group' => 'Writing Section',
                'passage' => 'Write an essay of at least 250 words.',
                'audio_path' => null,
                'created_at' => now(), 'updated_at' => now()
            ],
            // DẠNG 6: SPEAKING PART 2 (Tự luận nói - Thu âm trực tiếp)
            [
                'id' => 6,
                'question_text' => 'Describe a website you visit frequently. You should say: What it is, How often you visit it, What content it has, and explain why you find it useful.',
                'option_a' => null, 'option_b' => null, 'option_c' => null, 'option_d' => null,
                'correct_option' => null,
                'type' => 'Speaking Part 2',
                'assignment_id' => 1,
                'question_group' => 'Speaking Section',
                'passage' => 'You will have 1 minute to prepare and 2 minutes to record your speech.',
                'audio_path' => null,
                'created_at' => now(), 'updated_at' => now()
            ]
        ]);

        // 11. ĐỔ DỮ LIỆU BẢNG: submissions (Chấm điểm trực tiếp 4 kỹ năng và Overall)
        DB::table('submissions')->insert([
            [
                'id' => 1,
                'submission_content' => 'Học viên nộp bài thi thử Mock Test tháng 10',
                'file_path' => 'uploads/submissions/mocktest_hocviena.pdf',
                'grade' => 7.0,          // Điểm OVERALL
                'listening_score' => 7.5, // Điểm Nghe
                'reading_score' => 7.0,   // Điểm Đọc
                'writing_score' => 6.5,   // Điểm Viết
                'speaking_score' => 7.0,  // Điểm Nói
                'teacher_comment' => 'Kỹ năng nghe đọc khá tốt. Viết cần chú ý triển khai ý sâu hơn.',
                'status' => 'Đã chấm',
                'assignment_id' => 1,
                'user_id' => 3,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'id' => 2,
                'submission_content' => 'Bài làm của Học viên B',
                'file_path' => null,
                'grade' => 6.0,          // Điểm OVERALL
                'listening_score' => 6.0, // Điểm Nghe
                'reading_score' => 6.5,   // Điểm Đọc
                'writing_score' => 5.5,   // Điểm Viết
                'speaking_score' => 6.0,  // Điểm Nói
                'teacher_comment' => 'Điểm viết hơi thấp do lỗi ngữ pháp nhiều. Cần luyện tập thêm.',
                'status' => 'Đã chấm',
                'assignment_id' => 1,
                'user_id' => 4,
                'created_at' => now(), 'updated_at' => now()
            ]
        ]);

        // 12. ĐỔ DỮ LIỆU BẢNG: student_answers (Chi tiết bài làm tương ứng với từng kiểu câu hỏi)
        DB::table('student_answers')->insert([
            // Trả lời câu 1 (Nghe trắc nghiệm) -> Chọn đúng C
            ['id' => 1, 'question_id' => 1, 'submission_id' => 1, 'selected_option' => 'B', 'answer_text' => null, 'audio_path' => null, 'created_at' => now(), 'updated_at' => now()],
            
            // Trả lời câu 2 (Đọc T/F/NG) -> Chọn đúng False
            ['id' => 2, 'question_id' => 2, 'submission_id' => 1, 'selected_option' => 'False', 'answer_text' => null, 'audio_path' => null, 'created_at' => now(), 'updated_at' => now()],
            
            // Trả lời câu 3 (Điền từ vào chỗ trống) -> Điền văn bản ngắn
            ['id' => 3, 'question_id' => 3, 'submission_id' => 1, 'selected_option' => null, 'answer_text' => 'teachers', 'audio_path' => null, 'created_at' => now(), 'updated_at' => now()],
            
            // Trả lời câu 4 (Writing Task 1) -> Lưu nguyên bài luận miêu tả biểu đồ dài
            [
                'id' => 4, 'question_id' => 4, 'submission_id' => 1, 'selected_option' => null, 
                'answer_text' => 'The provided bar chart illustrates the proportion of households holding internet connectivity across various geographic regions over a ten-year period starting from 2015...', 
                'audio_path' => null, 'created_at' => now(), 'updated_at' => now()
            ],
            
            // Trả lời câu 5 (Writing Task 2) -> Lưu nguyên bài Essay nghị luận xã hội
            [
                'id' => 5, 'question_id' => 5, 'submission_id' => 1, 'selected_option' => null, 
                'answer_text' => 'In contemporary society, the integration of artificial intelligence into classrooms has sparked an intense debate. While some technocrats argue that AI mentors could surpass human teachers...', 
                'audio_path' => null, 'created_at' => now(), 'updated_at' => now()
            ],
            
            // Trả lời câu 6 (Speaking Part 2) -> Lưu đường dẫn file âm thanh học viên bấm ghi âm trực tiếp
            [
                'id' => 6, 'question_id' => 6, 'submission_id' => 1, 'selected_option' => null, 'answer_text' => null, 
                'audio_path' => 'uploads/submissions/speaking_test_u3_q6.mp3', 
                'created_at' => now(), 'updated_at' => now()
            ]
        ]);

        // 13. ĐỔ DỮ LIỆU BẢNG: feedback (Học viên gửi khiếu nại điểm số)
        DB::table('feedback')->insert([
            [
                'id' => 1,
                'feedback_content' => 'Thầy ơi tiêu chí LR (Từ vựng) em có dùng các từ idiom và từ C1/C2, thầy xem xét phúc khảo lại giúp em band này với ạ.',
                'old_grade' => 6.50,
                'new_grade' => 7.00,
                'teacher_reply' => 'Đã xem xét lại bài viết, các cụm từ collocations dùng rất tự nhiên. Thầy cập nhật lại điểm tổng lên 7.0 nhé.',
                'teacher_replied_at' => now(),
                'submission_id' => 2,
                'user_id' => 3,
                'created_at' => now(), 'updated_at' => now()
            ]
        ]);
    }
}