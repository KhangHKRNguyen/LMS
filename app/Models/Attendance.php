<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $table = 'attendances';

    protected $fillable = [
        'status', 'user_id', 
        'lesson_session_id' // Khóa ngoại mới chuyển đổi từ lớp sang buổi học
    ];

    // Bản điểm danh này thuộc về buổi học nào
    public function lessonSession()
    {
        return $this->belongsTo(LessonSession::class, 'lesson_session_id');
    }

    // Học viên được điểm danh
    public function student()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}