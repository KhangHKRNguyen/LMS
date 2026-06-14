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
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // Tên tài liệu
            $table->timestamp('uploaded_at')->useCurrent(); // Ngày upload
            $table->string('file_path'); // Đường dẫn file
            $table->string('file_type', 50)->nullable(); // Loại file (.pdf, .docx...)
            $table->foreignId('course_class_id')->constrained('course_classes')->onDelete('cascade'); // Mã lớp học
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Người upload
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materials');
    }
};
