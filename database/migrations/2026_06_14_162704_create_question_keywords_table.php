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
        Schema::create('question_keywords', function (Blueprint $table) {
            $table->id();
            $table->integer('blank_order'); // STT_ô_trống (Ô số 1, ô số 2...)
            $table->string('correct_keyword'); // Từ khóa đúng
            $table->foreignId('question_id')->constrained('questions')->onDelete('cascade'); // Mã câu hỏi gắn liền
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_keywords');
    }
};
