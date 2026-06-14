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
        Schema::create('learning_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Học viên
            $table->foreignId('course_class_id')->constrained('course_classes')->onDelete('cascade'); // Lớp học
            $table->decimal('midterm_grade', 4, 2)->nullable(); // Điểm giữa khóa
            $table->decimal('final_grade', 4, 2)->nullable(); // Điểm cuối khóa
            $table->date('approved_date')->nullable(); // Ngày phê duyệt
            $table->string('approval_status')->default('Chờ'); // Chờ / Đã duyệt
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('learning_results');
    }
};
