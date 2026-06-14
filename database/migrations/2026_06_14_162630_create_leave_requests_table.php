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
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->text('reason'); // Lý do vắng
            $table->string('attachment')->nullable(); // file đính kèm
            $table->timestamp('submitted_at')->useCurrent(); // ngày gửi đơn
            $table->string('status')->default('Chờ duyệt'); // Nháp / Chờ duyệt / Đã duyệt / Từ chối
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // người gửi
            $table->foreignId('approver_id')->nullable()->constrained('users')->onDelete('set null'); // người duyệt
            $table->foreignId('lesson_session_id')->constrained('lesson_sessions')->onDelete('cascade'); // Mã buổi học
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};
