<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Gọi file UserAndClassSeeder đã được sửa tên class ở trên
        $this->call([
            UserAndClassSeeder::class,
            // Thêm các class Seeder khác của bạn vào đây nếu có (ví dụ: AssignmentSeeder::class)
        ]);
    }
}