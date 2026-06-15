<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LearningResult extends Model
{
    use HasFactory;

    // Định danh tên bảng chính xác trong database
    protected $table = 'learning_results';

    // Cho phép ghi hàng loạt các cột dữ liệu này
    protected $fillable = [
        'user_id',
        'course_class_id',
        'midterm_grade',
        'final_grade',
        'approved_date',
        'approval_status',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function courseClass()
    {
        return $this->belongsTo(CourseClass::class, 'course_class_id');
    }
}