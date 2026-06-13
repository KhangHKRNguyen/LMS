<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id('id'); // Mã câu hỏi
            $table->text('question_text'); // Nội dung câu hỏi
            $table->text('option_a')->nullable(); // Đáp án a
            $table->text('option_b')->nullable(); // Đáp án b
            $table->text('option_c')->nullable(); // Đáp án c
            $table->text('option_d')->nullable(); // Đáp án d
            $table->string('correct_option')->nullable(); // Đáp án đúng (A, B, C hoặc D)
            $table->string('type')->nullable(); // Loại câu hỏi
            $table->foreignId('assignment_id')->constrained('assignments')->onDelete('cascade'); // Mã bài tập
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
