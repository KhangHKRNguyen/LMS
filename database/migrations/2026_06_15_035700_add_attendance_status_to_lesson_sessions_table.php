<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lesson_sessions', function (Blueprint $table) {
            $table->string('attendance_status')->default('chưa điểm danh')->after('course_class_id');
        });
    }

    public function down(): void
    {
        Schema::table('lesson_sessions', function (Blueprint $table) {
            $table->dropColumn('attendance_status');
        });
    }
};
