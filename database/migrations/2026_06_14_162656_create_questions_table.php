<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->integer('question_number'); // STT_CauHoi (Câu 1, Câu 2...)
            $table->string('question_type'); // Dạng câu hỏi (trac_nghiem, dien_tu, writing, speaking)
            $table->text('question_text')->nullable(); // Nội dung câu hỏi
            $table->decimal('points', 4, 2)->default(1.00); // Điểm số từng câu
            $table->integer('max_recording_time')->nullable(); // Giới hạn thời gian ghi âm (cho dạng Speaking)
            
            // 2 Khóa ngoại quan trọng kết nối Bài tập và Kỹ năng
            $table->foreignId('assignment_id')->constrained('assignments')->onDelete('cascade'); // Mã bài tập
            $table->foreignId('skill_id')->constrained('skills')->onDelete('cascade'); // Mã kỹ năng (Mới thêm)
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
