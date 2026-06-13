<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson_sessions', function (Blueprint $table) {
            $table->id(); // Mã buổi học
            // Khóa ngoại liên kết tới lớp học
            $table->foreignId('course_class_id')->constrained('course_classes')->onDelete('cascade');
            $table->date('session_date'); // Ngày học của buổi này
            $table->string('attendance_status')->default('chua_diem_danh'); // Tình trạng điểm danh
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_sessions');
    }
};