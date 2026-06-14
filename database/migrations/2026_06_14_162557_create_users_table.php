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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Họ tên
            $table->string('gender', 10)->nullable(); // Giới tính
            $table->date('dob')->nullable(); // Ngày sinh
            $table->string('phone', 20)->nullable(); // SĐT
            $table->string('avatar')->nullable(); // Ảnh
            $table->string('email')->unique(); // Email (Tài khoản)
            $table->string('password'); // Password
            $table->string('status')->default('active'); // Trạng thái tài khoản
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade'); // Mã vai trò
            $table->foreignId('qualification_id')->nullable()->constrained('qualifications')->onDelete('set null'); // Mã trình độ
            $table->timestamps(); // Bao gồm ngày tạo tài khoản (created_at)
        });
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration');
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('cache');
        Schema::dropIfExists('cache_locks');
    }
};
