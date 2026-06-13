<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Nâng cấp bảng questions (Đưa tài liệu đề bài xuống đây)
        Schema::table('questions', function (Blueprint $table) {
            // Lưu đoạn văn Reading của riêng câu hỏi/nhóm câu hỏi này, hoặc đề bài Writing Task 1/2
            $table->longText('passage')->nullable()->after('question_group');
            // Lưu file nghe .mp3 riêng của Section/Part này
            $table->string('audio_path')->nullable()->after('passage');
        });

        // 2. Nâng cấp bảng student_answers (Mở rộng để chứa mọi kiểu bài làm)
        Schema::table('student_answers', function (Blueprint $table) {
            $table->string('selected_option')->nullable()->change();
            // Thay vì dùng selected_option ngắn, thêm cột này để học viên điền từ hoặc viết nguyên bài Essay Task 1, 2
            $table->longText('answer_text')->nullable()->after('selected_option');
            // Lưu đường dẫn file bài nói (.mp3) do học viên thu âm trực tiếp từ trình duyệt
            $table->string('audio_path')->nullable()->after('answer_text');
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn(['passage', 'audio_path']);
        });

        Schema::table('student_answers', function (Blueprint $table) {
            $table->dropColumn(['answer_text', 'audio_path']);
        });
    }
};