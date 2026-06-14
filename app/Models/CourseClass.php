<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class CourseClass extends Model
{
    use HasFactory;

    protected $table = 'course_classes';

    protected $fillable = [
        'class_name',
        'start_date',
        'end_date',
        'room',
        'status',
        'course_id',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date'   => 'date',
        ];
    }

    // 1 Lớp thuộc về 1 Khóa học
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    // Quan hệ gốc: TẤT CẢ user trong lớp
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'class_user', 'course_class_id', 'user_id')
                    ->withTimestamps();
    }

    // Quan hệ lấy HỌC VIÊN (role_id = 4)
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'class_user', 'course_class_id', 'user_id')
                    ->where('users.role_id', 4);
    }

    // Quan hệ lấy 1 GIẢNG VIÊN đại diện (role_id = 2) để hiển thị ở table Index
    public function teacher(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'class_user', 'course_class_id', 'user_id')
                    ->where('users.role_id', 2)
                    ->limit(1);
    }

    public function materials(): HasMany
    {
        return $this->hasMany(Material::class, 'course_class_id');
    }

    /**
     * BỔ SUNG: 1 lớp học có nhiều buổi học (LessonSession)
     */
    public function lessonSessions(): HasMany
    {
        return $this->hasMany(LessonSession::class, 'course_class_id');
    }

    /**
     * BỔ SUNG: Lấy tất cả danh sách điểm danh của lớp thông qua các buổi học
     */
    public function attendances(): HasManyThrough
    {
        return $this->hasManyThrough(Attendance::class, LessonSession::class, 'course_class_id', 'lesson_session_id');
    }
}