<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    // Bổ sung mảng fillable để cho phép lưu dữ liệu khóa học
    protected $fillable = [
        'name',
        'description',
        'output_target',
        'duration'
    ];

    // Mối quan hệ: Một khóa học có nhiều lớp học
    public function courseClasses()
    {
        return $this->hasMany(CourseClass::class, 'course_id');
    }
}