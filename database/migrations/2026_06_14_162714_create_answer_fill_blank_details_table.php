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
        Schema::create('answer_fill_blank_details', function (Blueprint $table) {
            $table->id();
            $table->integer('blank_order'); // STT_ô_trống
            $table->string('student_input'); // Nội dung học viên điền
            $table->boolean('is_correct')->nullable(); // Hệ thống tự động đối chiếu chấm Đúng/Sai
            $table->foreignId('answer_fill_blank_id')->constrained('answers_fill_blank')->onDelete('cascade'); // Mã bài điền từ gốc
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('answer_fill_blank_details');
    }
};
