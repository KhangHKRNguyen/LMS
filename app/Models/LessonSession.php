<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\AssignmentDistribution;

class LessonSession extends Model
{
    protected $table = 'lesson_sessions';

    protected $fillable = [
        'course_class_id', 'lesson_date',
    ];

    // Thuộc về một lớp học cụ thể
    public function courseClass(): BelongsTo
    {
        return $this->belongsTo(CourseClass::class, 'course_class_id');
    }

    // Một buổi học có nhiều bản ghi điểm danh học viên
    public function attendances(): HasMany 
    {
        return $this->hasMany(Attendance::class, 'lesson_session_id');
    }

    // Một buổi học có nhiều đơn xin nghỉ từ các học viên khác nhau
    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class, 'lesson_session_id');
    }

    public function assignmentDistributions(): HasMany
    {
        return $this->hasMany(AssignmentDistribution::class, 'lesson_session_id');
    }
}