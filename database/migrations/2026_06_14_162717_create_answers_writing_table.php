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
        Schema::create('answers_writing', function (Blueprint $table) {
            $table->id();
            $table->longText('essay_content'); // Nội dung văn bản
            $table->integer('word_count')->nullable(); // Số từ
            $table->decimal('teacher_score', 4, 2)->nullable(); // Điểm giáo viên chấm
            $table->foreignId('submission_id')->constrained('submissions')->onDelete('cascade'); // Mã bài nộp
            $table->foreignId('question_id')->constrained('questions')->onDelete('cascade'); // Mã câu hỏi
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('answers_writing');
    }
};
