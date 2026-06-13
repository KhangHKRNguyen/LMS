<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_classes', function (Blueprint $table) {
            // Thêm khóa ngoại liên kết với bảng khóa học (courses)
            $table->foreignId('course_id')->nullable()->after('id')->constrained('courses')->onDelete('set null');
            // Thêm trạng thái lớp học (Ví dụ: Chưa bắt đầu, Đang học, Đã kết thúc)
            $table->string('status')->default('active')->after('room'); 
        });
    }

    public function down(): void
    {
        Schema::table('course_classes', function (Blueprint $table) {
            $table->dropForeign(['course_id']);
            $table->dropColumn(['course_id', 'status']);
        });
    }
};