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
        Schema::create('feedbacks', function (Blueprint $table) {
            $table->id();
            $table->text('content'); // Nội dung phản hồi
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Người phản hồi (Giáo viên/Học viên)
            $table->foreignId('submission_id')->constrained('submissions')->onDelete('cascade'); // Mã bài nộp
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedbacks');
    }
};
