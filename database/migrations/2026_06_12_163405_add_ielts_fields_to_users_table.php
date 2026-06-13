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
        Schema::table('users', function (Blueprint $table) {
            $table->string('gender')->nullable()->after('name'); // Giới tính
            $table->date('birthday')->nullable()->after('gender'); // Ngày sinh
            $table->string('phone')->nullable()->after('birthday'); // Số điện thoại
            $table->string('image')->nullable()->after('phone'); // Ảnh đại diện
            $table->string('qualification')->nullable()->after('role'); // Trình độ chuyên môn
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['gender', 'birthday', 'phone', 'image', 'qualification']);
        });
    }
};
