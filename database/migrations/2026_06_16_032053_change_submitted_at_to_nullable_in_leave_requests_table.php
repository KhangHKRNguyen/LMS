<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            // Chuyển cột submitted_at thành nullable
            $table->timestamp('submitted_at')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            // Khôi phục lại trạng thái ban đầu nếu rollback
            $table->timestamp('submitted_at')->useCurrent()->change();
        });
    }
};
