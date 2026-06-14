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
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // Tên bài tập
            $table->text('description')->nullable(); // Mô tả bài tập
            $table->string('file_path')->nullable(); // File đề bài
            $table->foreignId('assignment_type_id')->constrained('assignment_types')->onDelete('cascade'); // Mã loại bài tập
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Giáo viên tạo
            $table->timestamps(); // Bao gồm ngày tạo
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
