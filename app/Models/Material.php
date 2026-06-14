<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Material extends Model
{
    protected $table = 'materials';

    protected $fillable = [
        'title',
        'file_path',
        'course_class_id',
        'user_id',    // Nên bổ sung thêm các trường này vào fillable nếu sau này có dùng Material::create()
        'file_type'
    ];

    /**
     * Quan hệ N-1: Material thuộc 1 CourseClass (Lớp học)
     */
    public function courseClass(): BelongsTo
    {
        return $this->belongsTo(CourseClass::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
