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
        Schema::create('answers_speaking', function (Blueprint $table) {
            $table->id();
            $table->string('audio_file_path'); // Đường dẫn file âm thanh
            $table->integer('duration_seconds')->nullable(); // Thời lượng ghi âm (giây)
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
        Schema::dropIfExists('answers_speaking');
    }
};
