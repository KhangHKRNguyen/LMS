<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            // 1. Thêm liên kết đơn xin nghỉ vào đúng Buổi học thay vì cả Lớp học
            $table->foreignId('lesson_session_id')->nullable()->after('id')->constrained('lesson_sessions')->onDelete('cascade');
            
            // 2. Thêm đối tượng nhận (Người duyệt - thường là TA hoặc Admin)
            $table->foreignId('receiver_id')->nullable()->after('user_id')->constrained('users')->onDelete('set null');
            
            // 3. Thêm cột file đính kèm (minh chứng y tế/lý do vắng) và trạng thái đơn
            $table->string('file_path')->nullable()->after('reason'); 
            $table->string('status')->default('pending')->after('file_path'); // pending, approved, rejected

            // 4. Xóa bỏ liên kết cũ với bảng Lớp học
            if (Schema::hasColumn('leave_requests', 'course_class_id')) {
                $table->dropForeign(['course_class_id']);
                $table->dropColumn('course_class_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropForeign(['lesson_session_id', 'receiver_id']);
            $table->dropColumn(['lesson_session_id', 'receiver_id', 'file_path', 'status']);
        });
    }
};