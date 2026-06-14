<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    const STATUS_ACTIVE   = 'active';
    const STATUS_INACTIVE = 'inactive';
    protected $appends = ['role_text'];
    protected $fillable = [
        'id',
        'name',
        'email',
        'password',
        'role_id',          // Đồng bộ theo bảng roles mới
        'status',
        'gender',
        'dob',              // Thay cho birthday cũ
        'phone',
        'avatar',           // Thay cho image cũ
        'qualification_id', // Thay cho qualification cũ
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
            'dob'               => 'date',
        ];
    }

    // Liên kết tới bảng Roles mới
    public function roleRelation(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * ACCESSOR ĐỒNG BỘ NGƯỢC (BACKWARD COMPATIBILITY):
     * Khi gọi $account->role ở View, nó tự động trả về chuỗi 'admin', 'teacher', 'ta', 'student'
     * giúp giao diện Blade cũ chạy mượt mà không lo bị lỗi Crash.
     */
    public function getRoleAttribute(): string
    {
        $roleName = $this->roleRelation?->name;
        return $roleName === 'assistant' ? 'ta' : ($roleName ?? 'student');
    }

    public function getRoleTextAttribute(): string
    {
        $roleId = $this->role_id;
        
        return match($roleId) {
            1 => 'Quản trị viên',
            2 => 'Giảng viên',
            3 => 'Trợ giảng',
            4 => 'Học viên',
            default => 'Không xác định'
        };
    }

    // Sửa các helper kiểm tra quyền dựa trên role_id trong Seeder
    public function isAdmin(): bool
    {
        return $this->role_id == 1;
    }

    public function isTeacher(): bool
    {
        return $this->role_id == 2;
    }

    public function isTA(): bool
    {
        return $this->role_id == 3;
    }

    public function isStudent(): bool
    {
        return $this->role_id == 4;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function courseClasses(): BelongsToMany
    {
        return $this->belongsToMany(CourseClass::class, 'class_user', 'user_id', 'course_class_id')->withTimestamps();
    }

    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(CourseClass::class, 'class_user', 'user_id', 'course_class_id')->withTimestamps();
    }
}