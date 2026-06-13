<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Casts\Attribute;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    const ROLE_ADMIN   = 'admin';
    const ROLE_TEACHER = 'teacher';
    const ROLE_STUDENT = 'student';
    const ROLE_TA = 'ta';

    const STATUS_ACTIVE   = 'active';
    const STATUS_INACTIVE = 'inactive';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'name',
        'email',
        'password',
        'role',
        'status',
        'gender',
        'birthday',
        'phone',
        'image',
        'qualification',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isTeacher(): bool
    {
        return $this->role === self::ROLE_TEACHER;
    }

    public function isStudent(): bool
    {
        return $this->role === self::ROLE_STUDENT;
    }

    public function isTA(): bool
    {
        return $this->role === self::ROLE_TA;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Quan hệ 1-N: User có nhiều LeaveRequests (Đơn xin nghỉ)
     */
    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    /**
     * Quan hệ 1-N: User có nhiều Submissions (Bài nộp)
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    /**
     * Quan hệ 1-N: User có nhiều Attendances (Lịch sử điểm danh)
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Quan hệ 1-N: Người dùng gửi nhiều phản hồi
     */
    public function feedbacks(): HasMany
    {
        return $this->hasMany(Feedback::class);
    }

    /**
     * Quan hệ N-N: User có nhiều CourseClasses
     */
    public function courseClasses(): BelongsToMany
    {
        return $this->belongsToMany(CourseClass::class, 'class_user', 'user_id', 'course_class_id')->withTimestamps();
    }

    /**
     * Quan hệ N-N: User có nhiều CourseClasses
     */
    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(CourseClass::class, 'class_user', 'user_id', 'course_class_id')->withTimestamps();
    }
    /**
     * Danh sách các đơn xin nghỉ do User này (TA/Admin) xử lý duyệt
     */
    public function receivedLeaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class, 'receiver_id');
    }
}