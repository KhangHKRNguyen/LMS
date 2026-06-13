<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LessonSession extends Model
{
    protected $table = 'lesson_sessions';

    protected $fillable = [
        'course_class_id', 'session_date', 'attendance_status'
    ];

    // Thuộc về một lớp học cụ thể
    public function courseClass(): BelongsTo
    {
        return $this->belongsTo(CourseClass::class, 'course_class_id');
    }

    // Một buổi học có nhiều bản ghi điểm danh học viên
    public function attendances(): BelongsTo
    {
        return $this->hasMany(Attendance::class, 'lesson_session_id');
    }

    // Một buổi học có nhiều đơn xin nghỉ từ các học viên khác nhau
    public function leaveRequests(): BelongsTo
    {
        return $this->hasMany(LeaveRequest::class, 'lesson_session_id');
    }
}