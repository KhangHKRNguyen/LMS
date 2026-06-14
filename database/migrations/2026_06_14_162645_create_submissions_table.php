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
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->dateTime('submission_time'); // Thời gian nộp
            $table->decimal('total_grade', 4, 2)->nullable(); // Điểm số tổng quát (OverAll)
            
            // 4 Cột điểm thành phần mới bổ sung phục vụ tính điểm IELTS
            $table->decimal('listening_grade', 4, 2)->nullable(); // Điểm Listening
            $table->decimal('reading_grade', 4, 2)->nullable();   // Điểm Reading
            $table->decimal('writing_grade', 4, 2)->nullable();   // Điểm Writing
            $table->decimal('speaking_grade', 4, 2)->nullable();  // Điểm Speaking
            
            $table->text('teacher_comment')->nullable(); // Nhận xét của giáo viên
            $table->integer('attempt_number')->default(1); // Số lần làm lại bài
            $table->string('status')->default('submitted'); // Trạng thái (đã nộp / đã chấm)
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Học viên nộp
            $table->foreignId('assignment_distribution_id')->constrained('assignment_distributions')->onDelete('cascade'); // Mã lượt giao bài
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};
