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
        Schema::create('answers_multiple_choice', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_auto_correct')->nullable(); // Điểm tự động check (Đúng = True / Sai = False)
            $table->foreignId('submission_id')->constrained('submissions')->onDelete('cascade'); // Mã bài nộp
            $table->foreignId('question_id')->constrained('questions')->onDelete('cascade'); // Mã câu hỏi
            $table->foreignId('question_option_id')->constrained('question_options')->onDelete('cascade'); // Mã phương án lựa chọn
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('answers_multiple_choice');
    }
};
