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
        Schema::create('course_classes', function (Blueprint $table) {
            $table->id();
            $table->string('class_name'); // Tên lớp học
            $table->date('start_date')->nullable(); // Thời gian bắt đầu
            $table->date('end_date')->nullable(); // Thời gian kết thúc
            $table->string('room')->nullable(); // Phòng học
            $table->string('status')->default('active'); // Trạng thái lớp
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade'); // mã khóa học
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_classes');
    }
};
