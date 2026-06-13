<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            // Phân loại kỹ năng: listening, reading, writing, speaking
            $table->string('skill')->default('general')->after('type'); 
            // Lưu đường dẫn file âm thanh bài nghe (.mp3)
            $table->string('audio_path')->nullable()->after('skill'); 
            // Lưu nội dung bài đọc Reading hoặc đề bài Writing (Dùng longText vì bài đọc rất dài)
            $table->longText('passage')->nullable()->after('audio_path'); 
            // Thời gian giới hạn làm bài (tính bằng phút) phục vụ tính năng đếm ngược
            $table->integer('duration_minutes')->nullable()->after('due_time'); 
        });
    }

    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->dropColumn(['skill', 'audio_path', 'passage', 'duration_minutes']);
        });
    }
};