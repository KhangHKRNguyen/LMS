<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            // Thêm thẳng 4 cột điểm rõ ràng cho 4 kỹ năng
            $table->decimal('listening_score', 3, 1)->nullable()->after('grade');
            $table->decimal('reading_score', 3, 1)->nullable()->after('listening_score');
            $table->decimal('writing_score', 3, 1)->nullable()->after('reading_score');
            $table->decimal('speaking_score', 3, 1)->nullable()->after('writing_score');
            
            // Cột 'grade' có sẵn trong bảng của bạn sẽ đóng vai trò là điểm OVERALL.
        });
    }

    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropColumn(['listening_score', 'reading_score', 'writing_score', 'speaking_score']);
        });
    }
};