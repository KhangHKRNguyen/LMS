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
        Schema::create('grade_histories', function (Blueprint $table) {
            $table->id();
            $table->decimal('old_grade', 4, 2); // Điểm cũ
            $table->decimal('new_grade', 4, 2); // Điểm mới
            $table->timestamp('changed_at')->useCurrent(); // Ngày thay đổi
            $table->text('reason')->nullable(); // Lý do thay đổi
            $table->foreignId('question_id')->nullable()->constrained('questions')->onDelete('cascade'); // Mã câu hỏi
            $table->foreignId('submission_id')->constrained('submissions')->onDelete('cascade'); // Mã bài nộp
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Mã người dùng sửa điểm
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grade_histories');
    }
};
