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
        Schema::create('assignment_distributions', function (Blueprint $table) {
            $table->id();
            $table->integer('duration_minutes')->nullable(); // Thời gian làm bài (phút)
            $table->dateTime('open_time')->nullable(); // Thời gian mở
            $table->dateTime('close_time')->nullable(); // Thời gian đóng
            $table->integer('max_attempts')->default(1); // Giới hạn số lần làm lại bài
            $table->string('status')->default('Chưa mở'); // Chưa mở / Đang mở / Đã đóng
            $table->foreignId('lesson_session_id')->nullable()->constrained('lesson_sessions')->onDelete('cascade'); // Mã buổi học giao
            $table->foreignId('assignment_id')->constrained('assignments')->onDelete('cascade'); // mã bài tập
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Người giao (Giáo viên)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignment_distributions');
    }
};
