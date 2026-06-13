<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // 1. Thêm khóa ngoại liên kết tới bảng Buổi học (lesson_sessions)
            $table->foreignId('lesson_session_id')->nullable()->after('id')->constrained('lesson_sessions')->onDelete('cascade');

            // 2. Xóa bỏ liên kết cũ với bảng Lớp học (nếu có trường course_class_id)
            if (Schema::hasColumn('attendances', 'course_class_id')) {
                $table->dropForeign(['course_class_id']);
                $table->dropColumn('course_class_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['lesson_session_id']);
            $table->dropColumn('lesson_session_id');
        });
    }
};