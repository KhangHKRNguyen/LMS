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
        Schema::create('courses', function (Blueprint $table) {
            $table->id(); // Mã khóa học tự tăng
            $table->string('name'); // Tên khóa học
            $table->text('description')->nullable(); // Mô tả khóa học
            $table->string('output_target')->nullable(); // Mục tiêu đầu ra (Ví dụ: Cam kết 6.5)
            $table->string('duration')->nullable(); // Thời lượng khóa học
            $table->timestamps(); // Ngày tạo và ngày cập nhật
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
