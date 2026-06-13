<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseClass extends Model
{
    use HasFactory;

    protected $table = 'course_classes';

    protected $fillable = [
        'class_name',
        'start_time',
        'end_time',
        'room',
        'course_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time'   => 'datetime',
        ];
    }

    // ===================== RELATIONSHIPS =====================

    /**
     * Quan hệ 1-N: CourseClass có nhiều Materials (Tài liệu)
     */
    public function materials(): HasMany
    {
        return $this->hasMany(Material::class);
    }

    /**
     * Quan hệ 1-N: CourseClass có nhiều Assignments (Bài tập)
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    // Lấy tất cả đơn xin nghỉ của lớp này thông qua các Buổi học
    public function leaveRequests(): HasManyThrough
    {
        return $this->hasManyThrough(
            LeaveRequest::class, 
            LessonSession::class, 
            'course_class_id', // Khóa ngoại của CourseClass nằm trên bảng LessonSession
            'lesson_session_id' // Khóa ngoại của LessonSession nằm trên bảng LeaveRequest
        );
    }

    // Lấy tất cả lịch sử điểm danh của lớp này thông qua các Buổi học
    public function attendances(): HasManyThrough
    {
        return $this->hasManyThrough(
            Attendance::class, 
            LessonSession::class, 
            'course_class_id', 
            'lesson_session_id'
        );
    }

    /**
     * Quan hệ N-N: CourseClass có nhiều Users (Tất cả thành viên trong lớp)
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'class_user', 'course_class_id', 'user_id')->withTimestamps();
    }

    /**
     * Quan hệ N-N: Lọc danh sách các Giáo viên trong lớp (Số nhiều)
     */
    public function teachers(): BelongsToMany
    {
        return $this->users()->where('role', 'teacher');
    }

    /**
     * Quan hệ N-N: Lấy ra 1 Giáo viên phụ trách lớp học
     */
    public function teacher(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'class_user', 'course_class_id', 'user_id')
                    ->where('role', 'teacher')
                    ->limit(1);
    }

    /**
     * Quan hệ N-N: Lọc riêng các thành viên là Học viên trong lớp
     */
    public function students(): BelongsToMany
    {
        return $this->users()->where('role', 'student');
    }

    // Một lớp học thì thuộc về một Khóa học
    public function course(): BelongsTo {
        return $this->belongsTo(Course::class, 'course_id');
    }

    // Một lớp học thì có nhiều Buổi học
    public function lessonSessions()
    {
        return $this->hasMany(LessonSession::class, 'course_class_id');
    }

    // ===================== HELPER METHODS =====================

    /**
     * Kiểm tra lớp có thể xóa không - Chưa có học viên và bài tập
     */
    public function isDeletable(): bool
    {
        $hasStudents    = $this->students()->exists();
        $hasAssignments = $this->assignments()->exists();
        return !$hasStudents && !$hasAssignments;
    }
}