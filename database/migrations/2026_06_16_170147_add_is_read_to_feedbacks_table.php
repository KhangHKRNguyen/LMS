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
        Schema::table('feedbacks', function (Blueprint $table) {
            // Thêm cột is_read kiểu boolean (TINYINT(1)), mặc định là false (0)
            $table->boolean('is_read')->default(false)->after('submission_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('feedbacks', function (Blueprint $table) {
            // Xóa cột is_read nếu cần rollback migration
            $table->dropColumn('is_read');
        });
    }
};