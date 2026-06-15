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
        Schema::table('courses', function (Blueprint $table) {
            // Thêm cột output_overall (kiểu text, cho phép null và đặt sau cột output_target)
            $table->text('output_overall')->nullable()->after('output_target');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            // Xóa cột output_overall nếu rollback migration
            $table->dropColumn('output_overall');
        });
    }
};